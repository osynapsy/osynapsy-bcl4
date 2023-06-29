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

use Osynapsy\Html\Component\ComboBox as BaseComboBox;

class ComboBox extends BaseComboBox
{
    public function __construct($id, $class = '')
    {
        parent::__construct($id, trim('form-control '.$class));
    }

    public function enableSearch()
    {
        $this->addClass('selectpicker');
        $this->attribute('data-live-search', 'true');
        $this->requireCss('Bcl4/ComboBox/bootstrap-select.css');
        $this->requireJs('Bcl4/ComboBox/bootstrap-select.js');
    }

    public function setSmallSize()
    {
        $this->addClass('form-control-sm');
        return $this;
    }
}
