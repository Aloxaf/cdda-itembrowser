<?php

class BaseController extends Controller
{
    /**
     * Setup the layout used by the controller.
     */
    protected function setupLayout()
    {
        $this->layout = View::make("layouts.bootstrap");
    }
}
