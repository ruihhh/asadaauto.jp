<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // 大きな公開ページの描画を繰り返すと、循環参照のメモリがテスト間で回収されず
        // memory_limit（128MB）を超えることがあるため、テストごとに明示的に回収する。
        gc_collect_cycles();
    }
}
