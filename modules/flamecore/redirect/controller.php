<?php
namespace FlameCore\InfernumRedirectModule;

use FlameCore\Infernum\Application;
use FlameCore\Infernum\Controller as BaseController;
use Symfony\Component\HttpFoundation\Request;

class Controller extends BaseController
{
    private $target;

    protected function initialize(Application $app, $target)
    {
        $this->target = (string) $target;
    }

    public function action_index($params, Request $request, Application $app)
    {
        $url = preg_match('#^https?://#', $this->target) ? $this->target : $app->makePageUrl($this->target);

        return $this->redirect($url);
    }
}
