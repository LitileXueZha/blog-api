<?php

/**
 * 测试用
 * 
 * 函数编程之合成 Compose
 */

$func1 = function ($next) {
    return function () use ($next) {
        echo 1;
        $next();
        echo 1;
    };
};

$func2 = function ($next) {
    return function () use ($next) {
        echo 2;
        $next();
        echo 2;
    };
};
$func3 = function ($next) {
    return function () use ($next) {
        echo 3;
        $next();
        echo 3;
    };
};

$funcs = [$func3, $func2, $func1];

$func = array_reduce($funcs, function ($f, $g) {
    if (is_null($f)) return $g;

    return function ($next) use ($f, $g) {
        return $f($g($next));
    };
});

$func(function (){})();

