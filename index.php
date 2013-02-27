<?php

function globistr($string = '', $mbEncoding = ''/*optional e.g.'UTF-8'*/)
{
    //returns a case insensitive Version of the searchPattern for glob();
    // e.g.: globistr('./*.jpg') => './*.[jJ][pP][gG]'
    // e.g.: glob(dirname(__FILE__).'/'.globistr('*.jpg')) => '/.../*.[jJ][pP][gG]'
    
    // known problems: globistr('./[abc].jpg') => FALSE:'./[[aA][bB][cC]].[jJ][pP][gG]' 
    //(Problem: existing Pattern[abc] will be overwritten)
    // known solution: './[abc].'.globistr('jpg') => RIGHT: './[abc].[jJ][pP][gG]' 
    //(Solution: globistr() only caseSensitive Part, not everything)
    $return = "";
    if($mbEncoding !== ''){ //multiByte Version
        $string = mb_convert_case($string,MB_CASE_LOWER,$mbEncoding);
    }
    else{ //standard Version (not multiByte,default)
        $string = strtolower($string);
    }
    $mystrlen = strlen($string);
    for($i=0;$i<$mystrlen;$i++){
        if($mbEncoding !== ''){//multiByte Version
            $myChar = mb_substr($string,$i,1,$mbEncoding);
            //$myUpperChar = mb_strtoupper($myChar,$mbEncoding);
            $myUpperChar = mb_convert_case($myChar,MB_CASE_UPPER,$mbEncoding);
        }else{
            $myChar = substr($string,$i,1);
            $myUpperChar = strtoupper($myChar);
        }
        if($myUpperChar !== $myChar){ //there is a lower- and upperChar, / Char is case sentitive
            $return .= '['.$myChar.$myUpperChar.']'; //adding both Versions : [xX]
        }else{//only one case Version / Char is case insentitive
            $return .= $myChar; //adding '1','.','*',...
        }
    }
    return $return;
}

function glob_recursive($pattern, $flags = 0)
// Glob function for recursive folder search (looks in subfolders)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'\*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, glob_recursive($dir.'\\'.basename($pattern), $flags));
        }
        
        return $files;
}


function glob_files($filenames, $source_folder)
// Searches for video files with keyword(s) $filenames inside $source_folder
{
    if( !is_dir( $source_folder ) ) {
        die ( "Invalid directory.\n\n" );
    }
    
    $filenames = explode(' ', $filenames);
    $formats = array('mp4', 'avi', 'mkv');
    $key = 0;
    foreach ($filenames as $filename):
      foreach ($formats as $format):
        
        $FILES = glob_recursive($source_folder."\*".globistr($filename)."*.".$format);
        
        foreach($FILES as $file) {
            
            $FILE_LIST[$key]['path'] = substr( $file, 0, ( strrpos( $file, "\\" ) +1 ) );
            $FILE_LIST[$key]['name'] = substr( $file, ( strrpos( $file, "\\" ) +1 ) );    
            $FILE_LIST[$key]['size'] = filesize( $file );
            $FILE_LIST[$key]['date'] = date('Y-m-d G:i:s', filemtime( $file ) );
                $key++;
            
        }

    endforeach;
  endforeach;

    if (count($filenames) > 1):
        
        $SEARCH_MATCH = array();
        $MOVIE_NAMES = array();

        $matches = array();
        foreach ($FILE_LIST as $index => $f)
        {
            if (isset( $matches[$f['name']] ) && !in_array($f['name'], $MOVIE_NAMES))
            {
                array_push($SEARCH_MATCH, $f);
                array_push($MOVIE_NAMES, $f['name']);
            }
            $matches[$f['name']] = $index;
        }

    elseif (!empty($FILE_LIST)):
        $SEARCH_MATCH = $FILE_LIST;
    endif;

    if (!empty($SEARCH_MATCH))
    {
        return $SEARCH_MATCH;
    } 
    else 
    {
        return "No files found\n";
    }
}

if (isset($_POST['search'])):
  $search = $_POST['search'];
endif;

if (isset($search)):
  $videos = glob_files($search, 'videos');
endif;

if (isset($_GET['video'])):
  require 'getid3-1.9.3/getid3/getid3.php';

  $videopath = $_GET['video'];

  $getID3 = new getID3;
  $file = $getID3->analyze($videopath);
endif;
?>

<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <title>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="http://vjs.zencdn.net/c/video-js.css" rel="stylesheet">
    <style>
      body { padding-top: 60px; /* 60px to make the container go all the way
      to the bottom of the topbar */ }
      body {
        padding: 10px;
        background-color: #F4F4F4;
      }
      input[type=text]#herosearch {
          width: 600px;
          height: 50px;
          padding: 5px;
          padding-left: 10px;
          outline: none;
          border: 2px solid #999999;
          border-radius: 5px;
          background-color: #FBFBFB;
          font-family: Cambria, Cochin, Georgia, serif;
          font-size: 32px;
          -webkit-transition: background-color .2s ease-in,
                              background-position .2s ease-in,
                              box-shadow .3s ease-in;
           
          -moz-transition: background-color .2s ease-in,
                           background-position .2s ease-in,
                           box-shadow .3s ease-in;
           
          -o-transition: background-color .2s ease-in,
                         background-position .2s ease-in,
                         box-shadow .3s ease-in;
           
          -ms-transition: background-color .2s ease-in,
                          background-position .2s ease-in,
                          box-shadow .3s ease-in;
           
          transition: background-color .2s ease-in,
                      background-position .2s ease-in,
                      box-shadow .3s ease-in;          
      }
      input[type=text]#herosearch:focus {
          background-color: #FFFFFF;
          border-color: #333333;
          box-shadow: 0px 0px 25px -2px #333;
      }
    </style>
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js">
      </script>
    <![endif]-->
    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="bootstrap/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="bootstrap/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="bootstrap/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="bootstrap/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="bootstrap/ico/apple-touch-icon-57-precomposed.png">
    <style>
    </style>
  </head>
  <body>
    <div class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="index.php">
            video.local
          </a>
          <form class="navbar-form pull-right" method="post" action="index.php">
            <div>
              <input placeholder="Search movie title..." type="text" name="search">
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="hero-unit">
        <div>
          <?php
            if (isset($videopath)): ?>
              <video id="video_1" class="video-js vjs-default-skin" controls
                preload="auto" width="<?php echo $file['video']['resolution_x'] ?>" height="<?php echo $file['video']['resolution_y'] ?>" poster="my_video_poster.png"
                data-setup="{}">
                <source src="<?php echo $videopath ?>" type='video/mp4'>
              </video>
            <?php
            elseif (isset($search)):
              if (is_array($videos)):
                echo "search results for \"$search\"...<br />";
                foreach ($videos as $video):
                  printf('<a href="index.php?video=%s">%s</a><br />', $video['path'] . $video['name'], $video['name']);
                endforeach;
              else:
                echo $videos;
              endif;
            else: ?>
            <br /><br />
            <h1>Search movie title...<br /><br /></h1>
            <form method="post" action="index.php">
              <div>
                <input type="text" name="search" autofocus="autofocus" id="herosearch">
              </div>
            </form>
          <?php  
            endif;
          ?>
        </div>
      </div>
      <hr>
      <div>
        © Kimba's Castle
      </div>
    </div>

    <style>
      
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js">
    </script>
    <script src="assets/js/bootstrap.js">
    </script>
    <script src="http://vjs.zencdn.net/c/video.js"></script>
  </body>
</html>
