// Array.prototype.findIndex - MIT License (c) 2013 Paul Miller <http://paulmillr.com>
// For all details and docs: <https://github.com/paulmillr/Array.prototype.findIndex>
'use strict';
var ES = require('es-abstract/es6');

module.exports = function findIndex(predicate) {
	var list = ES.ToObject(this);
	var length = ES.ToLength(list.length);
	if (!ES.IsCallable(predicate)) {
		throw new TypeError('Array#findIndex: predicate must be a function');
	}
	if (length === 0) return -1;
	var thisArg = arguments[1];
	for (var i = 0, value; i < length; i++) {
		value = list[i];
		if (ES.Call(predicate, thisArg, [value, i, list])) return i;
	}
	return -1;
};
