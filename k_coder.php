<?php
//! Strcode is an utilty class useful for encoding and decoding strings using a convencional encoding algorithm.

class strcode
{
	var $m_Table; //Table used to encode and encode strings
	var $iW; //Width of the table
	var $iH; //Height of the table
	var $sKey; //String used to encode and decode a string

   //! Generates a random hex string
   /** \param $size: Length of generated string.
       \return generated string. */
   function f_randHex($size)
   {
        $retval="";
		for($i=0;$i<$size;$i++)
		{
			$selector=rand(0,1);
            if($selector==0)
                $r=rand(48,57);
           else if($selector==1)
                $r=rand(65,70);
           $retval.=chr($r);
		}
        return $retval;
   }

   //! Sets the boundaries of a value
   /** \param $n: value to set boundaries to.
       \param $max: Maximum value.
       \param $min: Minimum value.
       \param $tmax: Boundary limit.
       \return value.
       */
   function f_setboundaries($n,$max,$min,$tmax)
   {
   	$rv1=($max - $min + 1.0) * ($n / ($tmax + 1.0));
   	//$rv2=$max - $rv1;
   	$rval=$min + (int)$rv1;
   	//  $n = $min + (int)((($max) - ($min) + 1.0) * (($n) / (($tmax) + 1.0)));
   	  return $rval;
   }

	function f_rand($seed,$min,$max)
	{
	 $lval=3+($seed & 0x0FFF0000) >> 16;
    $hval=1+($seed & 0x0000FFFF) << 16;
	 $s=$lval . $hval . $seed;
	 $rval=0;
	 for($i=0;$i<strlen($s);$i++)
	 {
	 	$rval+=ord($s{$i});
	 }
    $rval+=$hval ^ $lval;
    $rval+=~($seed >> 3);
    $rval&=0x0FFFFFFF;
    $rval=$this->f_setboundaries($rval,$max,$min,0x0FFFFFFF);
    return $rval;
	}
	function t_create($width,$height)
	{
		if(is_array($this->m_Table))
		{
			unset($this->m_Table);
		}
		$this->m_Table=array();
		$this->iW=$width;
		$this->iH=$height;
	}
	function t_createforascii($iSeed=0)
	{
		$x=0;
		$inc=0xFF;
		$this->t_create(256,256);

		if($iSeed!=0)
		{

			$x=$iSeed;
		}
		for($i=0;$i<256;$i++)
		{
			$this->m_Table[]=array();
			for($j=0;$j<256;$j++)
			{
				$this->m_Table[$i][]=$x;
				if($iSeed!=0)
				{


					$x+=$inc;
					if($x>=0x10000)
					{
						$x-=0x10000;

					}
				   //echo $x . "<br>";
				}
				else
				{
					$x++;
				}

			}
		}



	 }

	function SetKey($val)
	{
		$this->sKey=$val;
	}
	function wordToHext($str,$wVal)
	{

		$retVal=sprintf("%4X",$wVal);
		/*for($i=0;$i<strlen($retVal);$i++)
		{
			if($retVal{$i}==" ")
				$retVal{$i}="0";
		}*/
		$retVal=str_replace(" ", "0", $retVal);
		$str.=$retVal;
		return $str;
	}
	function HexToWord($strval)
	{
		$len=strlen($strval);
		if(($len % 4)!=0)return;
		$size=$len/4;
		$counter=0;
		$temp="";
		$retvals=array();
		for($x=0;$x<$size;$x++)
		{

			 	$temp=substr($strval,$counter,4);

			 	$val=sscanf($temp,"%4X");

			 	$retvals[]=$val[0] ^ 0xFFFF;
			 	$counter+=4;

		}

		return $retvals;
	}



	function str_encode($val)
   {
   	$keylen=strlen($this->sKey);
   	$sourcelen=strlen($val);
        //Add seven fake bytes
		$stri=$this->f_randHex(7);
        $str="";
		$k=0;
   	for($i=0;$i<$sourcelen;$i++)
   	{
   		if($k==$keylen)$k=0;
   		$kval=ord($this->sKey{$k});
   		$sval=ord($val{$i});
   		$wval=$this->m_Table[$sval][$kval];
        //Extra trick XOR wval
        $wval= $wval ^ 0xFFFF;
   		$str=$this->wordToHext($str,$wval);

   		$k++;
   	}
    $str=$stri . $str;
    $str.=$this->f_randHex(3);//Add 3 end fake values
   	return $str;
   }

   function str_decode($coded_string)
   {
   	$keylen=strlen($this->sKey);
    //remove 7 first fake values from the string
    $rsize=strlen($coded_string)-10;
    $rstr=substr($coded_string,7,$rsize);

   	$values=$this->HexToWord($rstr);

   	$codelen=count($values);

   	$k=0;
   	$lastk=0;
   	$i=0;
   	$j=0;
		$decoded="";
   	while($i<$codelen)
   	{
   		if($j==$this->iW)
   		{

   			if($lastk==$k)
   			{
   				return "";
   			}
   			$j=0;
   		}
   		if($k==$keylen)$k=0;
   		$lastk=$k;
   		$kval=ord($this->sKey{$k});
   		if($values[$i]==$this->m_Table[$j][$kval])
   		{
   			$decoded.=chr($j);
   			$k++;
   			$i++;
   			$j=0;

   		}
   		else
   		{


 		  		$j++;
 		  	}

   	}

   	return trim($decoded);
	}
};


function genRandomKey($min=13,$max=18)
{
     srand();
  $r=rand($min,$max);
  $str="";
  for($i=0;$i<$r;$i++)
  {
      $opt=rand(1,3);
      switch($opt)
      {
        case 1:
            $str.=chr(rand(48,57));
            break;
        case 2:
            $str.=chr(rand(65,90));
            break;
        case 3:
            $str.=chr(rand(97,122));
            break;
       }
  }
  return $str;
}
?>

