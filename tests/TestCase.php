<?php

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function seeHasHeader($header)
    {
        $this
            ->assertTrue(
                $this->response->headers->has($header),
                "Response should have the header '{$header}' but does not."
            );

        return $this;
    }

    public function seeHeaderWithRegExp($header, $regexp)
    {
        $this
            ->seeHasHeader($header)
            ->assertRegExp(
                $regexp,
                $this->response->headers->get($header)
            );

        return $this;
    }

    public function linkFactory($count = 1)
    {
        $category = factory(\App\Category::class)->create();
        $links = factory(\App\Link::class, $count)->make();

        if ($count === 1) {
            $links->category()->associate($category);
            $links->save();
        } else {
            foreach ($links as $link) {
                $link->category()->associate($category);
                $link->save();
            }
        }

        return $links;
    }
}
