//
// This file shows the minimum you need to provide to BookReader to display a book
//
// Copyright(c)2008-2009 Internet Archive. Software license AGPL version 3.

// Create the BookReader object
br = new BookReader();


//Default Values
var pageWidth = 0;
var pageHeight = 0;
br.numLeafs = 0;
br.bookTitle= 'Wheaton College Library';
var author = '';
var description = '';
var directory = '/';
var indexMap = Array();
var lFirst = 0;
var cover;
var handle;

function setVals(width, height, numLeafs, title, aauthor, adescription, adirectory, aindexMap, alFirst, aCover, aHandle) {
  pageWidth = width;
  pageHeight = height;
  br.numLeafs = numLeafs;
  br.bookTitle = title;
  author = aauthor;
  description = adescription;
  directory = adirectory;
  indexMap = aindexMap;
  lFirst = alFirst;
  cover = aCover;
  handle = aHandle;
}

// Return the width of a given page.  Here we assume all images are 800 pixels wide
br.getPageWidth = function(index) {
    return pageWidth;
}

// Return the height of a given page.  Here we assume all images are 1200 pixels high
br.getPageHeight = function(index) {
    return pageHeight;
}

// We load the images from archive.org -- you can modify this function to retrieve images
// using a different URL structure
br.getPageURI = function(index, reduce, rotate) {
    var file = indexMap[index]["filename"];
    return (file == 'white') ? "Books/white.jpg" : directory + file;
}

// Return which side, left or right, that a given page should be displayed on
br.getPageSide = function(index) {
    if (0 == (index & 0x1)) {
        return (lFirst) ? 'L' : 'R';
    } else {
        return (lFirst) ? 'R' : 'L';
    }
}

// This function returns the left and right indices for the user-visible
// spread that contains the given index.  The return values may be
// null if there is no facing page or the index is invalid.
br.getSpreadIndices = function(pindex) {
    var spreadIndices = [null, null];
    if ('rl' == this.pageProgression) {
        // Right to Left
        if (this.getPageSide(pindex) == 'R') {
            spreadIndices[1] = pindex;
            spreadIndices[0] = pindex + 1;
        } else {
            // Given index was LHS
            spreadIndices[0] = pindex;
            spreadIndices[1] = pindex - 1;
        }
    } else {
        // Left to right
        if (this.getPageSide(pindex) == 'L') {
            spreadIndices[0] = pindex;
            spreadIndices[1] = pindex + 1;
        } else {
            // Given index was RHS
            spreadIndices[1] = pindex;
            spreadIndices[0] = pindex - 1;
        }
    }

    return spreadIndices;
}

// For a given "accessible page index" return the page number in the book.
//
// For example, index 5 might correspond to "Page 1" if there is front matter such
// as a title page and table of contents.
br.getPageNum = function(index) {
    return (indexMap[index] == null) ? index : indexMap[index]["semantic"];
}

// Total number of leafs
//br.numLeafs = 5;

// Book title and the URL used for the book title link
br.flipSpeed = 'medium';
br.bookUrl  = '';
br.logoURL = 'http://library.wheaton.edu';
br.mode = br.constMode2up;

// Override the path used to find UI images
br.imagesBaseURL = 'BookReader/images/';

br.buildShareDiv = function(jShareDiv) {
    var pageView = handle;
    var bookView = handle;
    var self = this;

    var jForm = $([
        '<p>Copy and paste one of these options to share this book elsewhere.</p>',
        '<form method="post" action="">',
            '<fieldset>',
                '<label for="booklink">Link to the book:</label>',
                '<input type="text" name="booklink" id="booklink" value="' + bookView + '"/>',
            '</fieldset>',
            '<fieldset>',
                '<label for="iframe">Embed a mini Book Reader:</label>',
                '<fieldset class="sub">',
                    '<label class="sub">',
                        '<input type="radio" name="pages" value="' + this.constMode1up + '" checked="checked"/>',
                        '1 page',
                    '</label>',
                    '<label class="sub">',
                        '<input type="radio" name="pages" value="' + this.constMode2up + '"/>',
                        '2 pages',
                    '</label>',
                    '<label class="sub">',
                        '<input type="checkbox" name="thispage" value="thispage"/>',
                        'Open to this page?',
                    '</label>',
                '</fieldset>',
                '<textarea cols="30" rows="4" name="iframe" class="BRframeEmbed"></textarea>',
            '</fieldset>',
            '<fieldset class="center">',
                '<button type="button" onclick="$.fn.colorbox.close();">Finished</button>',
            '</fieldset>',
        '</form>'].join('\n'));

    jForm.appendTo(jShareDiv);

    jForm.find('input').bind('change', function() {
        var form = $(this).parents('form:first');
        var params = {};
        params.mode = $(form.find('input[name=pages]:checked')).val();
        if (form.find('input[name=thispage]').attr('checked')) {
            params.page = self.getPageNum(self.currentIndex());
        }

        // $$$ changeable width/height to be added to share UI
        var frameWidth = "480px";
        var frameHeight = "430px";
        form.find('.BRframeEmbed').val(self.getEmbedCode(frameWidth, frameHeight, params));
    })
    jForm.find('input[name=thispage]').trigger('change');
    jForm.find('input, textarea').bind('focus', function() {
        this.select();
    });

    jForm.appendTo(jShareDiv);
    jForm = ''; // closure

}

br.getEmbedCode = function(frameWidth, frameHeight, viewParams) {
    return "<iframe src='" + handle + "' allowFullScreen webkitAllowfullScreen></iframe>";
}

br.blankInfoDiv = function() {
    return $([
        '<div class="BRfloat" id="BRinfo">',
            '<div class="BRfloatHead">About this book',
                '<a class="floatShut" href="javascript:;" onclick="$.fn.colorbox.close();"><span class="shift">Close</span></a>',
            '</div>',
            '<div class="BRfloatBody">',
                '<div class="BRfloatCover">',
                    '<img/>',
                '</div>',
                '<div class="BRfloatMeta">',
                    '<div class="BRfloatTitle">',
                        '<h2></h2>',
                    '</div>',
                    '<div class="BRfloatDescription">',
                        '<p></p>',
                    '</div>',
                '</div>',
            '</div>',
            '<div class="BRfloatFoot">',
                '<a href="https://openlibrary.org/dev/docs/bookreader" target="_blank">About the BookReader</a>',
            '</div>',
        '</div>'].join('\n')
    );
}

br.buildInfoDiv = function(jInfoDiv)
{
    if (cover != null)
      var coverPhoto = directory + cover;
    else
      var coverPhoto = 'Books/white.jpg';
    jInfoDiv.find('.BRfloatCover').append(['<div style="height: 140px; min-width: 80px; padding: 0; margin: 0;"><img src="' + coverPhoto + '" alt="' + this.title + '" height="140px" /></div>'].join(''));
    jInfoDiv.find('.BRfloatTitle h2').text(this.bookTitle);
    jInfoDiv.find('.BRfloatDescription p').text(description);
}

function runAfterInit() {
  $('#BookReader').find('.logo').attr('title', 'Go to library.wheaton.edu');
  $('#BRtoolbar').find('.read').hide();
  $('#textSrch').hide();
  $('#btnSrch').hide();
  $('#pageview').value = "test";

  $('#BRtoolbarbuttons').append($('<button>').addClass('BRicon full').attr({title: 'Go FullScreen'}).click(function() {
    if (document.fullscreenEnabled || document.mozFullScreenEnabled || document.documentElement.webkitRequestFullScreen) {
      if ( document.documentElement.requestFullscreen ) {
        document.documentElement.requestFullscreen();
      } else if ( document.documentElement.mozRequestFullScreen ) {
        document.documentElement.mozRequestFullScreen();
      } else if ( document.documentElement.webkitRequestFullScreen ) {
        document.documentElement.webkitRequestFullScreen( document.documentElement.ALLOW_KEYBOARD_INPUT );
      } else if (document.documentElement.msRequestFullscreen) {
        document.documentElement.msRequestFullscreen();
      }
      $('#BRtoolbarbuttons .full').hide();
      $('#BRtoolbarbuttons .fullClose').show();
    }
  }));
  $('#BRtoolbarbuttons').append($('<button>').addClass('BRicon fullClose').attr({title: 'Exit Fullscreen'}).click(function() {
    if (document.exitFullscreen ) {
      document.exitFullscreen();
    } else if (document.mozCancelFullScreen ) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen ) {
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen ) {
      document.msExitFullscreen();
    }
    $('#BRtoolbarbuttons .fullClose').hide();
    $('#BRtoolbarbuttons .full').show();
  }).hide());
}
// read-aloud and search need backend compenents and are not supported in the demo
/**/
