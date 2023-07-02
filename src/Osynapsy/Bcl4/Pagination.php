<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Bcl4;

use Osynapsy\Html\Tag;
use Osynapsy\Html\DOM;
use Osynapsy\Html\Component\AbstractComponent;
use Osynapsy\Html\Component\InputHidden;
use Osynapsy\Database\PaginatorSimple;

/**
 * Description of Pagination
 *
 * @author Pietro Celeste
 */
class Pagination extends AbstractComponent
{
    private $columns = [];
    private $entity = 'Record';
    protected $data = [];
    protected $pageDimensionPalceholder = '- Dimensione pagina -';
    private $db;
    private $filters = [];
    private $fields = [];
    private $loaded = false;
    private $par;
    private $sql;
    private $orderBy = null;
    private $parentComponent;
    private $position = 'center';
    private $statistics = [
        //Dimension of the pag in row;
        'pageDimension' => 10,
        'pageTotal' => 1,
        'pageCurrent' => 1,
        'rowsTotal' => 0
    ];
    private $pageDimensions = [
        1 => ['10', '10 righe'],
        2 => ['20', '20 righe'],
        5 => ['50', '50 righe'],
        10 => ['100', '100 righe'],
        20 => ['200', '200 righe']
    ];
    /**
     * Costructor of pager component.
     *
     * @param type $id Identify of component
     * @param type $pageDimension Page dimension in number of row
     * @param type $tag Tag of container
     * @param type $infiniteContainer Enable infinite scroll?
     */
    public function __construct($id, $pageDimension = 10, $tag = 'div', $infiniteContainer = false)
    {
        parent::__construct($tag, $id);
        $this->requireJs('bcl4/pagination/script.js');
        $this->addClass('BclPagination');
        if (!empty($infiniteContainer)) {
            $this->setInfiniteScroll($infiniteContainer);
        }
        if ($tag == 'form') {
            $this->attribute('method','post');
        }
        $this->setPageDimension($pageDimension);
        $this->add(new InputHidden($this->id))->addClass('BclPaginationCurrentPage');
        $this->add(new InputHidden($this->id.'OrderBy'))->addClass('BclPaginationOrderBy');
    }

    public function preBuild()
    {
        if (!$this->loaded) {
            $this->loadData();
        }

        foreach($this->fields as $field) {
            $this->add(new InputHidden($field, $field.'_hidden'));
        }
        list($pageMin, $pageMax) = $this->calcPageMinMax();
        $this->add($this->ulFactory($pageMin, $pageMax));
    }

    protected function calcPageMinMax()
    {
        $dim = min(7, $this->statistics['pageTotal']);
        $app = floor($dim / 2);
        $pageMin = max(1, $this->statistics['pageCurrent'] - $app);
        $pageMax = max($dim, min($this->statistics['pageCurrent'] + $app, $this->statistics['pageTotal']));
        $pageMin = min($pageMin, $this->statistics['pageTotal'] - $dim + 1);
        return [$pageMin, $pageMax];
    }

    protected function ulFactory($pageMin, $pageMax)
    {
        $ul = new Tag('ul', null, 'pagination pagination-sm justify-content-'.$this->position);
        $ul->add($this->liFactory('&laquo;', 'first', $this->statistics['pageCurrent'] < 2 ? 'disabled' : ''));
        for ($i = $pageMin; $i <= $pageMax; $i++) {
            $ul->add($this->liFactory($i, $i, $i == $this->statistics['pageCurrent'] ? 'active' : ''));
        }
        $ul->add($this->liFactory('&raquo;', 'last', $this->statistics['pageCurrent'] >= $this->statistics['pageTotal'] ? 'disabled' : ''));
        return $ul;
    }

    protected function liFactory($label, $value, $class)
    {
        $li = new Tag('li', null, trim('page-item '.$class));
        $li->add(new Tag('a', null, 'page-link'))
           ->attribute('data-value', $value)
           ->attribute('href','#')
           ->add($label);
        return $li;
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }

    public function addFilter($field, $value = null)
    {
        $this->filters[$field] = $value;
    }

    public function loadData($defaultPage = null)
    {
        $requestPage = filter_input(\INPUT_POST, $this->id) ?? $defaultPage;
        $sort = $this->getSort(filter_input(\INPUT_POST, $this->id.'OrderBy'));
        $pageDimension = $this->statistics['pageDimension'];
        $paginator = new PaginatorSimple($this->id.'Paginator', $this->db, $this->sql, $this->par);
        $this->data = $paginator->get($requestPage, $pageDimension, $sort);
        $this->statistics = $paginator->getAllMeta();
        $this->loaded = true;
        return $this->data;
    }

    public function getSort($requestSort)
    {
        $this->orderBy = empty($requestSort) ? $this->orderBy : str_replace(['][', '[', ']'], [',' ,'' ,''], $requestSort);
        return $this->orderBy;
    }

    public function getPageDimensionsCombo()
    {
        $fieldId = $this->pageDimensionFieldIdFactory();
        $Combo = new ComboBox($fieldId);
        $Combo->setPlaceholder(false);
        $Combo->attribute('onchange',"Osynapsy.refreshComponents(['{$this->parentComponent}'])");
        $Combo->setDataset($this->pageDimensions);
        $Combo->setValue($_REQUEST[$fieldId] ?? null);
        return $Combo;
    }

    protected function pageDimensionFieldIdFactory()
    {
        return $this->id . (strpos($this->id, '_') ? '_page_dimension' : 'PageDimension');
    }

    public function getInfo()
    {
        $end = min($this->getStatistic('pageCurrent') * $this->getStatistic('pageDimension'), $this->getStatistic('rowsTotal'));
        $start = ($this->getStatistic('pageCurrent') - 1) * $this->getStatistic('pageDimension') + 1;
        return sprintf(' %s - %s di %s %s', min($start, $end), $end, $this->getStatistic('rowsTotal'), strtolower($this->entity));
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function getTotal($key)
    {
        return $this->getStatistic('total'.ucfirst($key));
    }

    public function getStatistic($key = null)
    {
        return array_key_exists($key, $this->statistics) ? $this->statistics[$key] : null;
    }

    public function setInfiniteScroll($container)
    {
        $this->requireJs('Lib/imagesLoaded-4.1.1/imagesloaded.js');
        $this->requireJs('Lib/wookmark-2.1.2/wookmark.js');
        $this->attribute('class','infinitescroll',true)->attribute('style','display: none');
        if ($container[0] != '#' ||  $container[0] != '#') {
            $container = '#'.$container;
        }
        return $this->attribute('data-container',$container);
    }

    public function setOrder($field)
    {
        $this->orderBy = str_replace(['][', '[', ']'], [',' ,'' ,''], $field);
        return $this;
    }

    public function setPageDimension($pageDimension)
    {
        $comboId = $this->pageDimensionFieldIdFactory();
        if (!empty($_REQUEST[$comboId])) {
            $this->statistics['pageDimension'] = $_REQUEST[$comboId];
        } else {
            $_REQUEST[$comboId] = $this->statistics['pageDimension'] = $pageDimension;
        }
        if ($pageDimension === 10) {
            return;
        }
        foreach(array_keys($this->pageDimensions) as $key) {
            $dimension = $pageDimension * $key;
            $this->pageDimensions[$key] = [$dimension, "{$dimension}"];
        }
    }

    public function setPageDimensionPlaceholder($label)
    {
        $this->pageDimensionPalceholder = $label;
    }

    public function setParentComponent($componentId)
    {
        $this->parentComponent = $componentId;
        $this->attribute('data-parent', $componentId);
        return $this;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function setSql($db, $cmd, array $par = array())
    {
        $this->db = $db;
        $this->sql = $cmd;
        $this->par = $par;
        return $this;
    }

    public function getStatistics()
    {
        return $this->page;
    }
}
