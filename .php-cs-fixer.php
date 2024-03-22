<?php

declare(strict_types=1);

$factory = new Airlst\PhpCsFixerConfig\Factory([
    'src',
    'tests',
]);

return $factory
    ->customRules(['final_class' => true])
    ->create();
