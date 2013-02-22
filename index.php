<?php
if( !function_exists( 'array_flat' ) ) 
{ 
    function array_flat( $a, $s = array( ), $l = 0 ) 
    { 
        # check if this is an array 
        if( !is_array( $a ) )                           return $s; 
        
        # go through the array values 
        foreach( $a as $k => $v ) 
        { 
            # check if the contained values are arrays 
            if( !is_array( $v ) ) 
            { 
                # store the value 
                $s[ ]       = $v; 
                
                # move to the next node 
                continue; 
                
            } 
            
            # increment depth level 
            $l++; 
            
            # replace the content of stored values 
            $s              = array_flat( $v, $s, $l ); 
            
            # decrement depth level 
            $l--; 
            
        } 
        
        # get only unique values 
        if( $l == 0 ) $s = array_values( array_unique( $s ) ); 
        
        # return stored values 
        return $s; 
        
    } # end of function array_flat( ...    
}

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

if ( ! function_exists('glob_recursive'))
{
    // Does not support flag GLOB_BRACE
    
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).'\*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
        {
            $files = array_merge($files, glob_recursive($dir.'\\'.basename($pattern), $flags));
        }
        
        return $files;
    }
}

function glob_files($filenames, $source_folder)
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
?>

<!DOCTYPE html>
<html>
 
<head>
<meta charset="utf-8">
<title>video.local</title>
 
<style>
body {
    padding: 10px;
    background-color: #F4F4F4;
}
input[type=text] {
    width: 295px;
    padding: 5px;
    outline: none;
    border: 2px solid #999999;
    border-radius: 5px;
    background-color: #FBFBFB;
    font-family: Cambria, Cochin, Georgia, serif;
    font-size: 16px;
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
input[type=text]:focus {
    background-color: #FFFFFF;
    border-color: #333333;
    box-shadow: 0px 0px 25px -2px #333;
}
</style>
</head>
 
<body>
	<form method="post" action="index.php">
		<div>
			<input placeholder="Search movie title..." type="text" name="search" autofocus="autofocus">
		</div>
	</form>
	<br />
	<br />
	<?php
		if (isset($search)):
			if (is_array($videos)):
				echo "search results for \"$search\"...<br />";
				foreach ($videos as $video):
					printf('<a href="video.php?video=%s">%s</a><br />', $video['path'] . $video['name'], $video['name']);
				endforeach;
			else:
				echo $videos;
			endif;
		endif;
	?>
</body>
</html>