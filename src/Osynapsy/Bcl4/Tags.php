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

use Osynapsy\Html\Tag;
use Osynapsy\Html\Component;
use Osynapsy\Ocl\HiddenBox;

class Tags extends Component
{
    private $labelClass;
    private $modal;
    private $dropdown;
    private $hidden;
    private $autocomplete;

    public function __construct($name, $class = "badge badge-info")
    {
        parent::__construct('div', $name);
        $this->hidden = $this->add(new HiddenBox($name));
        $this->requireJs('Bcl4/Tags/script.js');
        $this->requireCss('Bcl4/Tags/style.css');
        $this->labelClass = $class;
    }

    public function __build_extra__()
    {
        $this->att('class','bclTags');
        $wrapper = $this->add(new Tag('div', null, 'bclTags-container d-inline'));
        if (!empty($_REQUEST[$this->id])) {
            $wrapper->add($this->tagsFactory($_REQUEST[$this->id]));
        }
        if (!empty($this->autocomplete)) {
            $this->add($this->autocomplete);
        }
        if (!empty($this->modal)) {
            $buttonAdd = $this->add(new Button('btn'.$this->id, '<span class="fa fa-plus"></span>', 'btn-info btn-xs'));
            $buttonAdd->att('data-toggle','modal')->att('data-target','#modal'.$this->id);
        }
        if (!empty($this->dropdown)) {
            $this->add($this->dropdown);
        }
    }

    protected function tagsFactory($strTags)
    {
        $result = new Tag('dummy');
        $tags = explode('][', $strTags);
        foreach($tags as $tag) {
            $result->add($this->tagFactory($tag));
        }
        return $result;
    }

    protected function tagFactory($tag)
    {
        $result = (new Tag('span', null, $this->labelClass))->att('data-parent',sprintf('#%s', $this->id));
        $result->add(str_replace(['[',']'], '', $tag));
        $result->add('&nbsp');
        $result->add(new Tag('span', null, 'fa fa-close bclTags-delete'));
        return $result;
    }

    protected function buttonModalCloseFactory()
    {
        $Button = new Button('clsModal'.$this->id, 'Annulla', 'btn-default pull-left float-left');
        $Button->att('data-dismiss','modal');
        return $Button;
    }

    public function addModal($title, $body, $buttonAdd)
    {
        $this->modal = $this->add(new Modal('modal'.$this->id, $title));
        $this->modal->addBody($body);
        $this->modal->addFooter($this->buttonModalCloseFactory());
        if (is_object($buttonAdd)) {
            $buttonAdd->att('class', 'bclTags-add', true)
                      ->att('data-parent', '#'.$this->id);
        }
        $this->modal->addFooter($buttonAdd);
    }

    public function addDropDown($label, $data)
    {
        $this->dropdown = new Dropdown($this->id.'_list', $label, 'span');
        $this->dropdown->setData($data);
    }

    public function addAutoComplete(array $data = [])
    {
        $ajax = filter_input(\INPUT_POST, 'ajax');
        if (empty($ajax)) {
            $this->autocomplete = new Autocomplete($this->id.'_auto','div');
            $this->autocomplete->addAutocompleteClass('input-group-sm');
            $this->autocomplete->att([
                'style' =>'width: 150px; margin-top: 3px;',
                'class' => 'd-inline-block'
            ]);
            $this->autocomplete->setSelected("\$('#{$this->id} span.fa-plus').click()");
            $this->autocomplete->setIco('<span class="fa fa-plus tag-append" onclick="BclTags.addTag(\'#'.$this->id.'\');"></span>');
            return $this->autocomplete;
        }
        if ($ajax != $this->id.'_auto') {
            return;
        }
        $Autocomplete = new Autocomplete($this->id.'_auto');
        $Autocomplete->setData($data);
        die($Autocomplete);
    }
}
