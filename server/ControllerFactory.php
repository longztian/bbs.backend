<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\core\Request;
use lzx\core\Response;
use site\Config;
use lzx\core\Logger;
use site\Session;
use site\HandlerRouter;

class ControllerFactory
{
    protected static $route = [];

    public static function create(Request $req, Response $response, Config $config, Logger $logger, Session $session)
    {
        list($cls, $args) = self::getHandlerClassAndArgs($req);

        if ($cls) {
            $handler = new $cls($req, $response, $config, $logger, $session);
            $handler->args = $args;
            return $handler;
        } else {
            // cannot find a controller
            $response->pageNotFound();
            throw new Exception();
        }
    }

    private static function getHandlerClassAndArgs(Request $req)
    {
        $args = self::getURIargs($req->uri);

        $keys = array_filter($args, function ($value) {
            return !is_numeric($value);
        });

        if (empty($keys)) {
            $keys[] = 'home';
        }

        $cls = null;
        while ($keys) {
            $key = implode('/', $keys);
            $cls = HandlerRouter::$route[$key];

            if ($cls) {
                break;
            } else {
                array_pop($keys);
            }
        }

        return [$cls, array_values(array_diff($args, $keys))];
    }

    private static function getURIargs($uri)
    {
        $parts = explode('?', $uri);
        $arg = trim($parts[0], '/');
        return array_values(array_filter(explode('/', $arg), 'strlen'));
    }
}
