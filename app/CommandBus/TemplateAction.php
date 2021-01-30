<?php

namespace App\CommandBus;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Opis\Events\EventDispatcher;
use LBHurtado\Missive\Routing\Router;
use LBHurtado\Missive\Models\Contact;
use LBHurtado\Missive\Classes\SMSAbstract;
use App\CommandBus\Middlewares\LogMiddleware;
use LBHurtado\Tactician\Classes\ActionAbstract;
use LBHurtado\Tactician\Contracts\ActionInterface;
use Joselfonseca\LaravelTactician\CommandBusInterface;

/**
 * Class TemplateAction
 * @package App\CommandBus
 *
 * TODO: factor this out in lbhurtado/missive
 */
class TemplateAction extends ActionAbstract implements ActionInterface
{
    /** @var Router */
    protected $router;

    /** @var string */
    protected $permission = 'issue command';

    /** @var array */
    protected $middlewares = [
        LogMiddleware::class
    ];

    /** @var array */
    protected $data = [];
    /**
     * TemplateAction constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        parent::__construct(
            app(CommandBusInterface::class),
            app(EventDispatcher::class),
            app(Request::class)
        );

        $this->router = $router;
    }

    /**
     * Add ['origin' => $origin] to $this->data for all actions
     */
    public function setup()
    {
        $this->addOriginToData()->addArgumentsToData();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function addArgumentsToData()
    {
        $params = Arr::get($this->arguments, 1);
        $this->data = array_merge($this->data, $params);

        return $this;
    }

    /**
     * @param null $permission
     * @return Contact|null
     */
    protected function permittedContact($permission = null)
    {
        $contact = tap($this->router->missive->getContact(), function (Contact $contact) {});

        return $contact->hasPermissionTo($permission ?? $this->permission) ? $contact : null; //TODO: if null then nice message
    }

    /**
     * @return $this
     */
    protected function addOriginToData()
    {
        $origin = $this->permittedContact();
        $this->data = array_merge($this->data, compact('origin'));

        return $this;
    }

    /**
     * @return $this
     */
    protected function addSMSToData()
    {
        $sms = $this->getSMS();
        $this->data = array_merge($this->data, compact('sms'));

        return $this;
    }

    /**
     * @return SMSAbstract
     */
    protected function getSMS(): SMSAbstract
    {
        return $this->router->missive->getSMS();
    }

    protected function addMiddleWare(string $middleware)
    {
        if (! in_array($middleware, $this->middlewares))
            array_push($this->middlewares, $middleware);

        return $this;
    }
}
