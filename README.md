# Someniatko Result Type Psalm Plugin

Provides more precise type support for `Result::all()` of [`someniatko/result-type` lib][library].

Supports Psalm 5 and PHP 7.4+.



## Installation

```
composer require --dev someniatko/result-type-psalm-plugin
```

Then add to your `psalm.xml`:

```xml
<psalm>
    <!-- other stuff -->
    <plugins>
        <!-- other plugins -->
        <pluginClass class="Someniatko\ResultTypePsalmPlugin\Plugin"/>
    </plugins>
</psalm>
```



[library]: https://packagist.org/packages/someniatko/result-type
