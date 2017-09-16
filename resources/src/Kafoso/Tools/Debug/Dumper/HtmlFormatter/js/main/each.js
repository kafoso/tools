var each = function(array, callback){
    for (var i=0;i<array.length; i++) {
        var result = callback(array[i], i);
        if (false === result) {
            break;
        }
    }
};
