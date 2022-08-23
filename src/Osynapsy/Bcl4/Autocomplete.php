<?php

/*
 * This file is part of the Osynapsy package.
 *
 * (c) Pietro Celeste <p.celeste@osynapsy.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Osynapsy\Bcl4;

use Osynapsy\Html\Component;
use Osynapsy\Html\Tag;
use Osynapsy\Ocl\HiddenBox;

class Autocomplete extends Component
{
    private $emptyMessage;
    protected $autocompleteclass = ['osy-autocomplete'];
    protected $ico = '<span class="fa fa-search"></span>';
    protected $db;
    protected $query = [
        'decode' => ['sql' => null, 'parameters' => []],
        'search' => ['sql' => null, 'parameters' => []]
    ];

    public function __construct($id, $db = null)
    {
        $this->requireJs('assets/Bcl4/Autocomplete/script.js');
        $this->requireCss('assets/Bcl4/Autocomplete/style.css');
        $this->db = $db;
        parent::__construct('div', $id);
    }

    public function __build_extra__()
    {
        if (filter_input(\INPUT_SERVER, 'HTTP_OSYNAPSY_HTML_COMPONENTS') != $this->id) {
            $this->addInput();
            return;
        }
        if (!empty($this->query['search']['sql'])) {
            $this->setData($this->db->find($this->query['search']['sql'], $this->query['search']['parameters']));
        }
        $this->addValueList();
    }

    private function addInput()
    {
        if (!empty($this->query['decode']['sql'])) {
            $_REQUEST[$this->id] = $this->db->findOne(
                $this->query['decode']['sql'],
                $this->query['decode']['parameters']
            );
        }
        $this->add($this->buildAutocomplete())
             ->add(new HiddenBox('__'.$this->id));
    }

    private function addValueList()
    {
        $valueList = $this->add(new Tag('div'));
        $valueList->att('id',$this->id.'_list');
        if (!empty($this->emptyMessage) && (empty($this->data) || !is_array($this->data))) {
            $valueList->add('<div class="row empty-message">'.$this->emptyMessage.'</div>');
            return;
        }
        foreach ($this->data as $rec) {
            $val = array_values($rec);
            if (empty($val) || empty($val[0])) {
                continue;
            }
            switch (count($val)) {
                case 1:
                    $val[1] = $val[2] = $val[0];
                    break;
                case 2:
                    $val[2] = $val[1];
                    break;
            }
            $src    = filter_input(\INPUT_POST,$this->id);
            $val[2] = str_replace($src,'<b>'.$src.'</b>',$val[2]);
            $valueList->add('<div class="row" data-value="'.$val[0].'" data-label="'.$val[1].'">'.$val[2].'</div>'.PHP_EOL);
        }
    }

    protected function buildAutocomplete()
    {
        $autocomplete = new InputGroup($this->id, '', $this->ico);
        $autocomplete->getTextBox()->onselect = 'event.stopPropagation();';
        return $autocomplete->setClass(implode(' ', $this->autocompleteclass));
    }

    public function setLabel($label)
    {
        $_REQUEST[$this->id] = $label;
        return $this;
    }

    public function setEmptyMessage($msg)
    {
        $this->emptyMessage = $msg;
        return $this;
    }

    public function setSelected($function)
    {
        $this->onselected = $function;
        return $this;
    }

    public function setUnSelected($function)
    {
        $this->onunselected = $function;
        return $this;
    }

    public function setIco($ico)
    {
        $this->ico = $ico;
    }

    public function setQuerySearch($query, $parameters)
    {
        $this->query['search']['sql'] = $query;
        $this->query['search']['parameters'] = $parameters;
        return $this;
    }

    public function setQueryDecodeId($query, $parameters)
    {
        $this->query['decode']['sql'] = $query;
        $this->query['decode']['parameters'] = $parameters;
        return $this;
    }

    public function addAutocompleteClass($class)
    {
        $this->autocompleteclass[] = $class;
    }
}
