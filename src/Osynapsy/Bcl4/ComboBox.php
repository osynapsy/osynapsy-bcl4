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

class ComboBox extends \Osynapsy\Ocl\ComboBox
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->setClass('form-control');
    }

    public function enableSearch()
    {
        $this->addClass('selectpicker');
        $this->att('data-live-search', 'true');
        $this->requireCss('assets/Bcl/ComboBox/bootstrap-select.css');
        $this->requireJs('assets/Bcl/ComboBox/bootstrap-select.js');
    }

    public function setSmallSize()
    {
        $this->setClass('form-control-sm');
        return $this;
    }
}
