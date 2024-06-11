<?php

namespace App\Http\Traits;

use App\ActionType;
use App\StatusState;

trait UploadTraits{
    public function getActionByDescription($action) {
        return ActionType::getType($action);
    }

    public function getStatusByDescription($status) {
        return StatusState::getState($status);
    }
}