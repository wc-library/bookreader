pageWidth = 800;
pageHeight = 700;
br.numLeafs = 5;
br.bookTitle = 'pngTest'

br.getPageURI = function(index, reduce, rotate) {

    var indexStr = (index + 1).toString();
    while (indexStr.length < 3) { indexStr = '0' + indexStr; }
    return '/bookreader/WheatonReader/Books/png/' + indexStr + '.png';
}
