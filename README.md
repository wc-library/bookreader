The Internet Archive BookReader is used to view books from the Internet Archive
online and can also be used to view other books.

Developer documentation:
http://openlibrary.org/dev/docs/bookreader

Hosted source code:
http://github.com/openlibrary/bookreader

The source code license is AGPL v3, as described in the LICENSE file.


-------------------------
Wheaton Reader:

Install:
	Clone this repository
	Run /WheatonReader/Books/init.sql
	Make sure that the webserver has access to read & write the following 3 directories:
		1) /BookMaker/tmp/
		2) /WheatonReader/Books/Images/
		3) /WheatonReader/Books/JSON/

HOW TO FORMAT ZIP FILE FOR BOOKMAKER
	This portion of the BookReader is used to create Books by uploading a zip file of page images
  		Supported image filetypes: png, jpg, and gif
  		Zip files should contain either only image files or 1 directory containing only image files.
  		Image files should be labeled: "prefix"_"semantic"_"pageIndex".jpg
    		The prefix can be whatever you would like it to be (it is ignored)
    		The semantic is what the page number actually is (eg. A, B, ii, 3)
    		The pageIndex is what page it is numberically (first would be 1 even it is labeled page A or i)
    		Example filenames: 'WC1860_A_1.jpg', 'JAMESANDTHEGIANTPEACH_72B_80.jpg', 'BIBLENIV_316_400.jpg'
