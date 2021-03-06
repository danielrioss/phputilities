<?php
/*
LICENSE.
Copyright (c) <2014> <'Daniel Rios'>

This software is provided 'as-is', without any express or implied
warranty. In no event will the authors be held liable for any damages
arising from the use of this software.

Permission is granted to anyone to use this software for any purpose,
including commercial applications, and to alter it and redistribute it
freely, subject to the following restrictions:

1. The origin of this software must not be misrepresented; you must not
   claim that you wrote the original software. If you use this software
   in a product, an acknowledgment in the product documentation would be
   appreciated but is not required.
2. Altered source versions must be plainly marked as such, and must not be
   misrepresented as being the original software.
3. This notice may not be removed or altered from any source distribution.

*/
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
	//! Utility function that creates a 16 bit random number given a seed.
	/** \param $seed: The seed.
	    \param $min: Minimum value.
	    \param $maximum value.
	    \return random number.
	    */
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
	//! Initilizes the internal table.
	/** \param $width: Width of the table.
	    \param $height: Height of table.
	    */
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
	//! Creates the encoding table using values from 0 to 65535.
	/** \param $iSeed: Determines whether the table values are created by a starting seed or just continous. */
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
	//! Sets the member sKey value.
	function SetKey($val)
	{
		$this->sKey=$val;
	}
	//! Converts a 16 bit value to a string hexidecimal value and appends it to str.
	/** \param $str: String where the new hexadecimal value would be appended.
	    \param $wVal: Word or 16 bit value.
	    \return String $str value + new hexadecimal value.*/
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
	//! Opposite of wordToHext function converts a hex string to an array of word values.
	/** \param $strval: String containing the hexadecimal string.
	    \return array with word values. */
	function HexToWord($strval)
	{
		$len=strlen($strval);
		if(($len % 4)!=0)return null;//length of string must be dividable by 4
		$size=$len/4;
		$counter=0;
		$temp="";
		$retvals=array();
		for($x=0;$x<$size;$x++)
		{

			 	$temp=substr($strval,$counter,4);

			 	$val=sscanf($temp,"%4X");

			 	$retvals[]=$val[0] ^ 0xFFFF;//Values were xor ed.
			 	$counter+=4;

		}

		return $retvals;
	}


	//! Encodes a string value.
	/** \param $val: Value to be encoded.
	    \return new encoded value.
	*/
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
		  //we use each ascii value as a coordinate and use that value from the table as the encoded value.
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
	//! Decodes a string previously encoded by str_encode.
	/** \param $coded_string: Encoded string.
	    \return decoded string or empty string if failed. */
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

//! Additional function creates a random key.
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

