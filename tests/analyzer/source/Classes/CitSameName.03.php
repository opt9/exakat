<?php

namespace A {
    interface T {}
    trait T {}
}

namespace {
    class T {}
    interface T {}

}

namespace B {
    trait T {}
}

?>