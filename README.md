# BookReader
The Internet Archive BookReader is used to view books from the Internet Archive
online and can also be used to view other books.

Developer documentation:
http://openlibrary.org/dev/docs/bookreader

Hosted source code:
http://github.com/openlibrary/bookreader

The source code license is AGPL v3, as described in the LICENSE file.


-------------------------
## Wheaton Reader v0.2

### Installation
1. Configure files listed below such that the webclient can write to them
2. Run /install.php

#### Files/directories the webclient needs write access to

* /BookMaker/config.json/
* /BookMaker/tmp/
* /WheatonReader/Books/Images/
* /WheatonReader/Books/JSON/

#### Dependencies
-   Zip (http://php.net/manual/en/book.zip.php)


### Usage
#### Create a book

1. Start at /index.php or /BookMaker/index.php
2. Give a Title, a Zip file with the image files (see [Upload Format](#upload-format) section), and optionally an author & description
3. Click 'Continue'
4. Select a cover (thumbnail) photo
5. Choose placement of first page (left/right)
6. Check the names and order of every page (updating names if needbe)
7. Click 'Create Book'

#### View a book

1. Start at /index.php or /BookMaker/index.php
2. Select Archive from the navbar at the top
3. Find the book you'd like to view by scrolling or searching
4. Click on the book itself

#### Embed a book

1. Start at /index.php or /BookMaker/index.php
2. Select Archive from the navbar at the top
3. Find the book you'd like to embed by scrolling or searching
4. Click 'Embed Code'
5. Copy and paste the snippet into your site

#### Edit a book

1. Start at /index.php or /BookMaker/index.php
2. Select Archive from the navbar at the top
3. Find the book you'd like to edit by scrolling or searching
4. Click 'Edit'
5. Follow the directions for Creation

#### Delete a book

1. Start at /index.php or /BookMaker/index.php
2. Select Archive from the navbar at the top
3. Find the book you'd like to delete by scrolling or searching
4. Click 'Delete'
5. Press 'OK' to confirm


#### Upload Format

* Upload 1 zip file containing image files (.png, .jpg, or .gif)
* Name all files in the format: `"prefix"_"index"_"semantic".ext`
	- eg. `'GulliversTravels_0006_intro iv.png'` will be the 6th page with the page name 'intro iv' at the bottom
	- eg. `'_0008_8.jpg'` will be the 8th page with the page name '8' at the bottom
