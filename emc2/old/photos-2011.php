<?php
	$pg_name	= "Pictures";

	include 'frame_top.php';

	chdir('pictures');

	$pic_guts = 5;
	// $pic_cool = 1;
	$pic_awrd = 5;

	if ($_GET["c"] !== NULL)
	{
		$gallery = $_GET["c"];
		$photo = intval($_GET["n"]);

		$to_previous = "c=$gallery&n=" . ($photo - 1);
		$to_next     = "c=$gallery&n=" . ($photo + 1);

		if ($gallery == "guts")
		{
			$image = sprintf("Guts_%02d.JPG", $photo);

			if ($photo == 1)
			{
				$to_previous = '';
			}
			if ($photo == $pic_guts)
			{
				$to_next = "c=cool&n=1";
			}
		}
		else if ($gallery == "cool")
		{
			$image = sprintf("Cooldown_%02d.JPG", $photo);

			if ($photo == 1)
			{
				$to_previous = "c=guts&n=$pic_guts";
			}
			if ($photo == $pic_cool)
			{
				$to_next = "c=awrd&n=1";
			}
		}
		else if ($gallery == "awrd")
		{
			$image = sprintf("Awards_%02d.JPG", $photo);

			if ($photo == 1)
			{
				$to_previous = "c=cool&n=$pic_cool";
			}
			if ($photo == $pic_awrd)
			{
				$to_next = '';
			}
		}

		$captionpath = '/var/www/emc2/pictures/2011/captions/' . $image . '.txt';
		if ( file_exists($captionpath) )
		{
			$caption = file_get_contents($captionpath);
		}

		echo <<<HTML
<p class="topright"><a href="photos.php">Back to Photo Gallery</a></p>

<h1>$image</h1>

<table>
  <tr>
    <td style="text-align:left; width:100px">
    	<a href='photos.php?{$to_previous}'>
	  &lt;&lt;Previous
	</a>
    </td> <td style="text-align:center">
    	<a href='photos.php'>
          (Gallery)
	</a>
    </td> <td style="text-align:right; width:100px">
    	<a href='photos.php?{$to_next}'>
	  Next&gt;&gt;
	</a>
    </td>
  </tr> <tr>
    <td colspan=3 style='img_800x800'>
    	<a href='photos.php?{$to_next}'>
    	  <img src='pictures/2011/$image' alt='$image'>
	</a>
    </td>
  </tr> <tr>
    <td colspan=3 style='text-align:left; whitespace:pre; max-width:800px'>
	$caption
    </td>
  </tr> 
</table>
HTML;

	}
	else
	{
?>

<h1>Contest Pictures</h1>

<h2>Guts Round</h2>

<table>
  <tr>
<?php
		for ($i = 1; $i <= $pic_guts; $i++)
		{
			if ($i % 5 == 1 && $i > 1)
			{
				echo "  </tr> <tr>\n";
			}

			$i < 10 ? $j = '0' . $i : $j = $i;

			echo <<<HTML
    <td class="img_thumbnail">
    	<a href='photos-2011.php?c=guts&n=$i'>
    	  <img src='pictures/2011/thumbs/Guts_$j.png' alt='Guts_$j.JPG'>
	</a>
    </td>
HTML;
		}
	
?>
  </tr>
</table>

<h2>Cooldown Round</h2>

<table>
  <tr>
<?php
		for ($i = 1; $i <= $pic_cool; $i++)
		{
			if ($i % 5 == 1 && $i > 1)
			{
				echo "  </tr> <tr>\n";
			}

			$i < 10 ? $j = '0' . $i : $j = $i;

			echo <<<HTML
    <td class="img_thumbnail">
    	<a href='photos.php?c=cool&n=$i'>
    	  <img src='pictures/2011/thumbs/Cooldown_$j.png' alt='Cooldown_$j.JPG'>
	</a>
    </td>
HTML;
		}
	
?>
  </tr>
</table>

<h2>Awards Ceremony</h2>

<table>
  <tr>
<?php
		for ($i = 1; $i <= $pic_awrd; $i++)
		{
			if ($i % 5 == 1 && $i > 1)
			{
				echo "  </tr> <tr>\n";
			}

			$i < 10 ? $j = '0' . $i : $j = $i;

			echo <<<HTML
    <td class="img_thumbnail">
    	<a href='photos.php?c=awrd&n=$i'>
    	  <img src='pictures/2011/thumbs/Awards_$j.png' alt='Awards_$j.JPG'>
	</a>
    </td>
HTML;
		}
	
?>
  </tr>
</table>

<?php
	}

	include 'frame_bottom.php';
?>
