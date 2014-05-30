![Brainy](https://gitenterprise.inside-box.net/mbasta/brainy/raw/fixes/documentation/brainy.png)

Brainy is a replacement for the popular [Smarty](http://www.smarty.net/)
templating language. It is a fork from the Smarty 3 trunk.


## Why Brainy?

- Brainy generates cleaner and faster code than Smarty by default.
- Brainy removes features that are infrequently used and increase code bloat.
  Brainy's feature set promotes best practices and discourages hacks.
- Brainy is safer than Smarty in that it removes support for features like
  `eval` and injecting arbitrary PHP into a template.


## Where is Brainy headed?

- **Phase 1**: Provide a clean, drop-in replacement for Smarty that generates
  cleaner code and increases code quality.
- **Phase 2**: Provide a backwards-compatible interface for allowing templates
  to compile asynchronously.
- **Phase 3**: Allow templates to be compiled to [Hack](http://hacklang.org/)
  and add full async support.


## Differences from Smarty

While Brainy will work as a drop-in replacement for Smarty in most
applications, there are some differences that may make it difficult to switch.


### Incompatibilities

- Inline and arbitrary PHP is disallowed for security reasons.
  - PHP tags: `<?php ?>`
  - Shorthand PHP tags: `<? ?>`
  - ASP tags: `<% %>`
  - PHP blocks: `{php}`
  - `{eval}`
  - `{include_php}`
- Backticks in template strings no longer function like curly braces in PHP.
- Caching backends are removed (MySQL, Memcached).
- `nocache` is always set to `true` and cannot be disabled.
- Some other features are removed:
  - `{fetch}` is removed as it can result in unforseen performance and security
    issues.
  - `{debug}` is removed as it can reveal sensitive information.
- Whitespace surrounding tags is not always treated the same as in Smarty.

Additionally, undefined variables do not throw errors (similar to Smarty 2's
behavior). For example:

```php
{if $foo}{$bar}{/if}
```

If either `$foo` or `$bar` are undefined, the template will simply return an
empty string. In Smarty 3, the behavior is to throw an undefined index error.