<?php

namespace Shibalike;

interface ICondition {

    /**
     * @return bool
     */
    public function isSatified();
}