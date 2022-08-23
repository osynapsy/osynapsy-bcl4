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

use Osynapsy\Ocl\ComboBox;

/**
 * Description of Select
 *
 * @author Peter
 */
class Select extends ComboBox
{
    //put your code here
    public function __construct($name, $multiple = false, $title = null)
    {
        parent::__construct($name);
        $this->class = 'osy-select';
        $this->requireCss('assets/Lib/bootstrap-select-1.10.0/bootstrap-select.css');
        $this->requireJs('assets/Lib/bootstrap-select-1.10.0/bootstrap-select.js');
        $this->requireJs('assets/Bcl4/Select/script.js');
        //$this->setParameter('option-select-disable',false);
        if ($multiple) {
            $this->setMultiSelect();
        }
        if ($title) {
            $this->title = $title;
        }
    }

    public function setMultiSelect()
    {
        $this->att('multiple','multiple');
        if (strpos($this->name,'[') === false) {
            $this->name = $this->name.'[]';
        }
        return $this;
    }
}
