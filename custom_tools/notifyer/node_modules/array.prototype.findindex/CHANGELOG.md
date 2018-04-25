# 2.0.2
 - [Performance] the entry point should use the native function when compliant

# 2.0.1
 - [Fix] use call instead of apply in bound entry point function (#17)
 - [Refactor] Remove unnecessary double ToLength call (#16)
 - [Tests] run tests on travis-ci

# 2.0.0
 - [Breaking] use es-shim API (#13)
 - [Docs] fix example in README (#9)
 - [Docs] Fix npm install command in README (#7)

# 1.0.0
 - [Fix] do not skip holes, per ES6 change (#4)
 - [Fix] Older browsers report the typeof some host objects and regexes as "function" (#5)

# 0.1.1
 - [Fix] Support IE8 by wrapping Object.defineProperty with a try catch (#3)
 - [Refactor] remove redundant enumerable: false (#1)

# 0.1.0
 - Initial release.
