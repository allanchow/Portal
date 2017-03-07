<?php

namespace App\Http\ViewComposers;

use App\Model\helpdesk\Settings\Company;
use App\Model\helpdesk\Ticket\Tickets;
use App\Model\Cdn\Cdn_Resources;
use App\Model\helpdesk\Agent_panel\User_org;
use Auth;
use Illuminate\View\View;

class ClientLayout
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $company;
    protected $tickets;
    protected $resources;
    protected $user_org;

    /**
     * Create a new profile composer.
     *
     * @param
     *
     * @return void
     */
    public function __construct(Company $company, Tickets $tickets, Cdn_Resources $resources, User_org $user_org)
    {
        $this->company = $company;
        $this->auth = Auth::user();
        $this->tickets = $tickets;
        $this->resources = $resources;
        $this->user_org = $user_org;
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        $notifications = \App\Http\Controllers\Common\NotificationController::getNotifications();
        $view->with([
            'company'         => $this->company,
            'notifications'   => $notifications,
            'tickets'         => $this->tickets(),
            'resources'       => $this->resources(),
            'user_org'        => $this->user_org,
        ]);
    }

    public function tickets()
    {
        return $this->tickets->select('id', 'ticket_number');
    }

    public function resources()
    {
        return $this->resources->select('id', 'org_id');
    }
}
