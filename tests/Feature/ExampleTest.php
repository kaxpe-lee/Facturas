<?php

test('redirects unauthenticated users to login from home', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});