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

class ListTree extends ListBox
{
    private $groups = array();
    public $data = array();
    private $request = null;
    private $icon = [
        'open' => 'fa fa-chevron-down',
        'close' => 'fa fa-chevron-right'
    ];

    public function __construct($id)
    {
        parent::__construct($id);
        $this->requireJs('Bcl4/ListBox/script.js');
        $this->requireCss('Bcl4/ListBox/style.css');
    }

    protected function __build_extra__()
    {
        $this->request = empty($_REQUEST[$this->id]) ? null : $_REQUEST[$this->id];
        array_unshift($this->data,array('','- seleziona -'));
        $this->add($this->buildBranch($this->data));
    }

    private function buildBranch($branch, $class='listbox-list')
    {
        if (!$branch) {
            return null;
        }
        $ul = new Tag('ul');
        $ul->att('class',$class);

        foreach ($branch as $rec) {
            $hasSublist = array_key_exists($rec[0], $this->groups);
            $li = $ul->add(new Tag('li'));
            $li->add(new Tag('div'))
               ->att('value',$rec[0])
               ->att('class','listbox-list-item'.($rec[0] == $this->request ? ' selected': ''))
               ->add(($hasSublist ? '<small><span class="'.$this->icon['close'].'"></span></small> ': '').$rec[1]);
            if ($hasSublist) {
                if ($childs = $this->buildBranch($this->groups[$rec[0]], 'listbox-sublist hidden d-none')) {
                    $li->add($childs);
                }
            }
        }
        return $ul;
    }

    public function SetData($rawData)
    {
        $this->data = array();
        foreach ($rawData as $k => $rec) {
            if (empty($rec[2])) {
                $this->data[] = $rec;
            } else {
                $this->groups[$rec[2]][] = $rec;
            }
        }
        //$this->buildBranch($this->data);
    }
}