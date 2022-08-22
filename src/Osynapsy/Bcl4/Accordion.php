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

use Osynapsy\Html\Component as Component;
use Osynapsy\Html\Tag;
use Osynapsy\Ocl\HiddenBox;

//Costruttore del pannello html
class Accordion extends Component
{
    private $panels = array();
    private $defaultOpen  = 0;

    public function __construct($id, $defaultOpen = 0)
    {
        parent::__construct('div', $id);
        $this->requireCss('assets/Bcl4/Accordion/style.css');
        $this->att('class','accordion osy-panel-accordion')
             ->att('role','tablist');
        $this->requireCss('assets/Bcl/PanelAccordion/style.css');
        //$this->requireJs('Bcl/PanelAccordion/script.js');
        $memoryOpen = filter_input(\INPUT_POST, $this->id);
        $this->defaultOpen = is_null($memoryOpen) ? $defaultOpen : $memoryOpen;
    }

    public function __build_extra__()
    {
        $this->add(new HiddenBox($this->id));
        foreach($this->panels as $panel) {
            $this->add($panel);
        }
    }

    public function addPanel($title, $commands = [])
    {
        $panelIdx = count($this->panels);
        $panelId = $this->id.'_'.$panelIdx;
        $open = $this->defaultOpen === $panelIdx ? true : false;
        //$panelHd = '<a data-toggle="collapse" data-parent="#'.$this->id.'" href="#'.$panelId.'-body" data-panel-id="'.$panelIdx.'" class="'.(filter_input(\INPUT_POST, $this->id) == $panelIdx ? 'collapsed' : '').'" onclick="">'.$title.'</a>';
        $panelHd = $this->buildHeader($title, $panelId.'_body', $open);
        $panel = new PanelNew($panelId, $panelHd);
        $panel->setClass(
            'card-body collapse'.($open ? ' show' : ''),
            'card-header',
            'card-foot',
            'card',
            ''
        );
        $panel->addCommands($commands)->getBody()->att([
            'id' => $panelId.'_body',
            'data-parent' => '#'.$this->id
        ]);
        $this->panels[] = $panel;
        return $this->panels[$panelIdx];
    }

    private function buildHeader($title, $targetId, $open)
    {
        $span = new Tag('span', null, 'c-pointer');
        $span->att([
            //'type' => 'button',
            'data-toggle' => 'collapse',
            'role' => 'button',
            'data-target' => '#'.$targetId,
            'aria-expanded' => empty($open) ? 'false' : 'true',
            'aria-controls' => $targetId
        ])->add($title);
        return $span;
    }
}
