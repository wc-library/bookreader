pageWidth = 991;
pageHeight = 1318;
br.numLeafs = 635;
br.bookTitle = 'Greek Bible'

br.getPageURI = function(index, reduce, rotate) {
    var indexStr = (index + 1).toString();
    while (indexStr.length < 3) { indexStr = '0' + indexStr; }
    return 'Books/Low Res/page '+ indexStr + ' LOW RES.jpg';
}
