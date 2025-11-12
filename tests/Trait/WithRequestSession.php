<?php

namespace Tests\Trait;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

trait WithRequestSession
{

    public function createSession(KernelBrowser $client): Session
    {
        $cookie = $client->getCookieJar()->get('MOCKSESSID');

        // create a new session object
        $container = $client->getContainer();
        $session = $container->get('session.factory')->createSession();

        if ($cookie) {
            // get the session id from the session cookie if it exists
            $session->setId($cookie->getValue());
            $session->start();
        } else {
            // or create a new session id and a session cookie
            $session->start();
            $session->save();

            $sessionCookie = new Cookie(
                $session->getName(),
                $session->getId(),
                null,
                null,
                'localhost',
            );
            $client->getCookieJar()->set($sessionCookie);
        }

        return $session;
    }

}