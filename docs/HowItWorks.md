# How Does Rector Work?

(Inspired by [*How it works* in BetterReflection](https://github.com/Roave/BetterReflection/blob/master/docs/how-it-works.md))

## 1. Finds all files and Load Configured Rectors

- The application finds files in source you provide and registeres Rectors - from `--level`, `--configur` or local `rector.yml`
- Then it iterates all found files and applies relevant Rectors to them.
- *Rector* in this context is 1 single class that modifies 1 thing, e.g. changes class name

## 2. Parse and Reconstruct 1 File

The iteration of files, nodes and Rectors respects this life cycle:

```php
foreach ($fileInfos as $fileInfo) {
    // 1 file => nodes
    $nodes = $phpParser->parseFileInfo($fileInfo);

    // nodes => 1 node
    foreach ($nodes as $node) { // rather traverse all of them
        foreach ($rectors as $rector) {
            if ($rector->isCandidate($node)) {
                $rector->reconstruct($node);
            }
        }
    }
}
```

### 2.1 Prepare Phase

- File is parsed by [`nikic/php-parser`](https://github.com/nikic/PHP-Parser), 4.0-dev (this is important, because this version support writing modified tree back to file)
- Then nodes (array of objects by parser) are traversed by `StandaloneTraverseNodeTraverser` to prepare it's metadata, e.g. class name, method node the node is in, namespace name etc. added by `$node->setAttribute(Attribute::CLASS_NODE, 'value')`.

### 2.2 Rectify Phase

- When all nodes are ready, applicies iterates all active Rector
- Each nodes is passed to `$rector->isCandidate($node)` method to see, if this Rector should do some work on it, e.g. is this class name called `OldClassName`?
- If it doesn't match, it goes to next node.
- If it matches, the `$rector->reconstruct($node)` method is called
- Active Rector changes all what he should and returns changed node

### 2.2.1 Order of Rectors

- Rectors are run by they natural order in config, first will be run first.

E.g. in this case, first will be changed `@expectedException` annotation to method,
 then a method `setExpectedException` to `expectedException`.

```yml
# rector.yml
rectors:
    Rector\Rector\Contrib\PHPUnit\ExceptionAnnotationRector: ~

    Rector\Rector\Dynamic\MethodNameReplacerRector:
        'PHPUnit\Framework\TestClass':
            'setExpectedException': 'expectedException'
            'setExpectedExceptionRegExp': 'expectedException'
```

### 2.3 Save File/Diff Phase

- When work on all nodes of 1 file is done, the file will be saved if it has some changes
- Or if the `--dry-run` option is on, it will store the *git-like* diff thanks to [GeckoPackages/GeckoDiffOutputBuilder](https://github.com/GeckoPackages/GeckoDiffOutputBuilder)
- Then go to next file

## 3 Reporting

- After this Rector displays list of changed files
- Or with `--dry-run` option the diff of these files
