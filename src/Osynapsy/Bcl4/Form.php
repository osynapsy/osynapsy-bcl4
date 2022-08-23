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
use Osynapsy\Data\Dictionary;

/**
 * Represents a Html Form.
 *
 * @author Pietro Celeste <p.celeste@osynapsy.org>
 */
class Form extends Component
{
    use FormCommands;

    protected $head;
    protected $headCommandWidth = 12;
    public  $headClass = 'row';
    protected $alert;
    protected $alertCount=0;
    protected $body;
    protected $foot;
    protected $footClass;
    protected $footStyle;
    protected $footLeft;
    protected $footRight;
    protected $repo;
    protected $headCommand;
    protected $appendFootToMain = false;

    public function __construct($name, $mainComponent = 'PanelTk', $tag = 'form', $bsv = 4)
    {
        parent::__construct($tag, $name);
        $this->repo = new Dictionary(['foot' => ['offset' => 1, 'width' => 10]]);
        //Form setting
        $this->att(['name' => $name, 'method' => 'post', 'role' => 'form']);
        //Body setting
        $this->body = $this->buildMainComponent($mainComponent);
        if ($mainComponent === 'PanelTk' && $bsv === 4) {
            $this->body->setClasses('card', 'card-header', 'card-body', 'card-footer');
        }
    }

    protected function __build_extra__()
    {
        if ($this->head) {
            $this->add($this->head);
        }
        if ($this->alert) {
            $this->add($this->alert);
        }
        $this->add($this->body);
        //Append foot
        if (!$this->foot) {
            return;
        }
        if ($this->appendFootToMain) {
            $this->body->put(
                '',
                $this->foot->get(),
                10000,
                10,
                $this->repo->get('foot.width'),
                $this->repo->get('foot.offset')
            );
            return;
        }
        $this->add($this->foot->get());
    }

    protected function buildMainComponent($mainComponent)
    {
        $rawComponent = '\\Osynapsy\\Bcl4\\'.$mainComponent;
        $component = new $rawComponent($this->id.'_panel', 'div');
        $component->setParameter('label-position','inside');
        return $component;
    }

    public function addCard($title)
    {
        $this->body->addCard($title);
    }

    public function addHeadCommand($object, $space = 1)
    {
        if (empty($this->headCommand)) {
            $this->headCommand = $this->head($this->headCommandWidth);
            $this->headCommand->att('style','padding-top: 10px');
        }
        if ($space > 0) {
            $this->headCommand->add(str_repeat('&nbsp;', $space));
        }
        $this->headCommand->add($object);
    }

    public function head()
    {
        if (empty($this->head)) {
            $this->head = new Tag('div', null, 'd-flex flex-md-row flex-column block-header m-b');
        }
        return $this->head->add(new Tag('div', null, 'p-2'));
    }

    public function alert($label = null, $type = 'danger')
    {
        if (empty($this->alert)) {
            $this->alert = new Tag('div');
            $this->alert->att('class','transition animated fadeIn m-b-sm');
        }
        $alert = new Alert('alert_'.$this->alertCount, $label, $type);
        $alert->setDismissible(true);
        $alert->showIcon(true);
        $this->alert->add($alert);
        $this->alertCount++;
        return $alert;
    }

    public function fixCommandBar($class = 'fixed-bottom p-2 b-light')
    {
        if (empty($this->foot)) {
            $this->footClass = $class;
            $this->footStyle = 'background-color: rgba(255,255,255,0.8); border-top: 1px solid #ddd;';
            $this->addClass('mb-5');
            return;
        }
        $this->foot->addClass($class);
        $this->foot->att('style', 'background-color: rgba(255,255,255,0.8); border-top: 1px solid #ddd;');
    }

    public function foot($obj, $right = false)
    {
        if (empty($this->foot)) {
            $this->foot = new Tag('div', null, trim('d-flex mt-2 '.$this->footClass));
            $this->foot->style = $this->footStyle;
            $this->footLeft = $this->foot->add(new Tag('div', null, 'p-1 mr-auto'));
            $this->footRight = $this->foot->add(new Tag('div', null, 'p-1'));
        }
        $column = $right ? $this->footRight : $this->footLeft;
        $column->add($obj);
        return is_object($obj) ? $obj : $column;
    }

    public function getPanel()
    {
        return $this->body;
    }

    public function put($lbl, $obj, $x = 0, $y = 0, $width = 1, $offset = null, $class = '')
    {
        $this->body->put($lbl, $obj, $x, $y, $width, $offset, $class);
        return $this->body;
    }

    public function setCommand($delete = false, $save = true, $back = true, $closeModal = false, $fixbar = false)
    {
        if ($back) {
            $this->foot($this->buttonBackFactory());
        }
        if ($closeModal) {
            $this->foot($this->buttonCloseModalFactory());
        }
        if ($delete) {
            $this->foot($this->buttonDeleteFactory($delete), true);
        }
        if ($save) {
            $this->foot(is_object($save) ? $save : $this->buttonSaveFactory($save), true);
        }
        if ($fixbar) {
            $this->fixCommandBar();
        }
    }

    public function setType($type)
    {
        if ($type == 'horizontal') {
            $this->att('class','form-horizontal',true);
        }
        $this->body->setType($type);
    }

    public function setTitle($title, $subTitle = null, $size = 6, $hsize = 'h2')
    {
        $objTitle = new Tag($hsize);
        $objTitle->add($title);
        $column = $this->head($size);
        $column->addClass('mr-auto')->add($objTitle);
        //$this->headCommandWidth -= $size;
        if (!empty($subTitle)) {
            $column->add('<h4><i>'.$subTitle.'</i></h4>');
        }
        return $objTitle;
    }

    public function parameter($key, $value=null)
    {
        if (is_null($value)){
            return $this->repo->get($key);
        }
        $this->repo->set($key, $value);
        return $this;
    }

    public function resetClass()
    {
        $this->body->setClasses('', '', '', '');
    }
}
