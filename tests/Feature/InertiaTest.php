<?php

use Inertia\Testing\AssertableInertia;

/**
 * Inertia の土台のテスト。
 *
 * 個々の画面ではなく、全ページに共通で載るもの (ログインユーザー、ナビ、
 * フラッシュ) と、Blade との混在が壊れていないことを見る。
 *
 * 共有データの組み立ては App\Http\Middleware\HandleInertiaRequests にある。
 */

beforeEach(function () {
    actingAsUser(createUser(['name' => '共有太郎', 'email' => 'shared@example.com']));
});

test('ログインユーザーが共有データに載る', function () {
    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.name', '共有太郎')
        ->where('auth.email', 'shared@example.com')
        // Blade では md5(session('email')) を直接書いていた
        ->where('auth.avatarUrl', 'https://www.gravatar.com/avatar/' . md5('shared@example.com') . '?s=56&d=mp'));
});

test('ナビの現在位置がサーバー側で決まる', function () {
    // Blade では request()->route()->named() をビューの中で呼んでいた
    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('nav', 4)
        ->where('nav.2.label', '発注書')
        ->where('nav.2.active', true)
        ->where('nav.0.active', false));
});

test('ユーザーメニューの URL が共有データに載る', function () {
    // Ziggy のようなルートヘルパを入れず、URL はサーバーで組んで渡す
    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('menu.files', url('/downloader'))
        ->where('menu.users', url('/users'))
        ->where('menu.logout', url('/logout')));
});

test('フラッシュメッセージが共有データに載る', function () {
    createCustomer();

    $this->post('/orders/create', orderPayload())->assertRedirect('/orders');

    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('flash.message', '発注書を作成しました')
        ->where('flash.status', 'success')
        ->where('flash.icon', 'check-circle-fill'));
});

test('フラッシュが無いときは null になる', function () {
    $this->get('/orders')->assertInertia(fn (AssertableInertia $page) => $page->where('flash', null));
});

test('バリデーションエラーが共有データに載る', function () {
    // Inertia は 302 で戻し、次のリクエストの props.errors に載せる
    $this->post('/orders/create', orderPayload(['order_no' => '']))
        ->assertRedirect();

    $this->get('/orders/create')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('errors.order_no'));
});

test('未認証では Inertia の画面もログインへリダイレクトされる', function () {
    session()->flush();

    $this->get('/orders')->assertRedirect('/login');
});

test('Blade のままの画面も動く', function () {
    // 混在している間はどちらも壊れていないことを見ておく。
    // 移行が済んだらこのテストごと消える。
    foreach (['/', '/settings', '/downloader', '/lumo-academy'] as $path) {
        $this->get($path)->assertOk();
    }
});

test('移行済みの画面は Inertia を返す', function () {
    $expected = [
        '/orders' => 'Orders/Index',
        '/customers' => 'Customers/Index',
        '/projects' => 'Projects/Index',
        '/users' => 'Users/Index',
        '/trips' => 'Trips/Index',
        '/expenses' => 'Expenses/Index',
    ];

    foreach ($expected as $path => $component) {
        $this->get($path)->assertInertia(fn (AssertableInertia $page) => $page->component($component));
    }
});
