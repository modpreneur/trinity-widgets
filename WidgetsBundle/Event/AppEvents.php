<?php

/*
 * This file is part of the Trinity project.
 */

namespace Trinity\WidgetsBundle\Event;

/**
 * Class AppEvents.
 */
class AppEvents
{
    // navigation
    const MENU_CREATE = 'app.main_menu.configure';
    const QUICK_MENU_CREATE = 'app.quick_menu.configure';

    // widgets
    const WIDGET_INIT = 'app.widget_init';
    const WIDGET_TYPE_INIT = 'app.widget_type_init';
}
