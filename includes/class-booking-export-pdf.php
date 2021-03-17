<?php

/**
 * Handle CSV
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Booking_Export
 * @subpackage Booking_Export/includes
 */

/**
 * Handle CSV
 *
 * This class defines all code necessary to generate CSV
 *
 * @since      1.0.0
 * @package    Booking_Export
 * @subpackage Booking_Export/includes
 * @author     Your Name <email@example.com>
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

use Fpdf\Fpdf;

class Booking_Export_PDF {
  public static function generate($filename, $owner, $period, $data, $amount) {
    $pdf = new CustomPDF('p', 'mm', 'A4'); // 210x297
    $pdf->owner = $owner;
    $pdf->period = $period;

    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 9);
    $pdf->ImprovedTable(
      ['Propriété', 'Client', 'Date d\'arrivée', 'Date de départ', 'Nombre de nuit', 'Prix TTC'], 
      $data,
    );
    $pdf->TotalAmount($amount);
    $pdf->Output($filename, 'D');
  }
}

class CustomPDF extends FPDF {
  public $owner;
  public $period;

  function Header() {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
    
    $this->Image($image[0], 65, 4, 70);

    $this->SetFont('Helvetica', 'B', 12);
    $this->SetXY(10, 40);
    $this->cell(50, 10, $this->textDecoded('Synthèse du ' . $this->period[0] . ' au ' . $this->period[1]), 0, 0, 'L', 0);

    $this->SetFont('Helvetica', 'B', 12);

    $this->SetXY(160, 40);
    $this->cell(40, 8, $this->textDecoded('A l\'attention de'), 0, 0, 'R', 0);

    $this->SetFont('Helvetica', '', 12);
    $this->SetXY(160, 48);
    $this->cell(40, 8, $this->textDecoded('Mr. ' . $this->owner['name']), 0, 0, 'R', 0);
  }

  function Footer() {
    $this->SetY(-20);
    $this->SetFont('Helvetica', 'B', 12);
    $this->Cell(0, 6, $this->textDecoded('Park Stay'), 0, 0, 'C');
    $this->Ln();
    $this->SetFont('Helvetica', '', 12);
    $this->Cell(0, 6, $this->textDecoded('393 Rue Charles Lindberg, 34130 Mauguio'), 0, 0, 'C');
    $this->Ln();
    $this->Cell(0, 6, $this->textDecoded('Numéro de SIRET: 123456789123 - Numéro de TVA: FR123456789'), 0, 0, 'C');
  }

  function TotalAmount($amount) {
    $this->SetX(10);
    $this->cell(190, 8, $this->textDecoded('Montant total TTC: ' . $amount . ' €'), 1, 0, 'R', 0);
  }

  // Better table
  function ImprovedTable($header, $data)
  {
    $this->SetXY(10, 60);
    
      // Column widths
      $w = array(40, 40, 30, 30, 25, 25);
      // Header
      for($i=0;$i<count($header);$i++)
          $this->Cell($w[$i],7,$this->textDecoded($header[$i]),1,0,'C');
      $this->Ln();
      // Data
      foreach($data as $row)
      {
          $this->SetX(10);
          $this->CellFit($w[0],6,$this->textDecoded($row[0]),1);
          $this->CellFit($w[1],6,$this->textDecoded($row[1]),1);
          $this->CellFit($w[2],6,$this->textDecoded($row[2]),1);
          $this->CellFit($w[3],6,$this->textDecoded($row[3]),1);
          $this->CellFit($w[4],6,$this->textDecoded($row[4]),1);
          $this->CellFit($w[5],6,$this->textDecoded($row[5]),1, 0, 'R');
          $this->Ln();
      }
      // Closing line
      // $this->Cell(array_sum($w),0,'','T');
  }

  function textDecoded($str) {
    return iconv('UTF-8', 'windows-1252', $str);
  }

  //Cell with horizontal scaling if text is too wide
  function CellFit($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='', $scale=false, $force=false)
  {
      //Get string width
      $str_width=$this->GetStringWidth($txt);

      //Calculate ratio to fit cell
      if($w==0)
          $w = $this->w-$this->rMargin-$this->x;
      $ratio = ($w-$this->cMargin*2)/$str_width;

      $fit = ($ratio < 1 || ($ratio > 1 && $force));
      if ($fit)
      {
          if ($scale)
          {
              //Calculate horizontal scaling
              $horiz_scale=$ratio*100.0;
              //Set horizontal scaling
              $this->_out(sprintf('BT %.2F Tz ET',$horiz_scale));
          }
          else
          {
              //Calculate character spacing in points
              $char_space=($w-$this->cMargin*2-$str_width)/max(strlen($txt)-1,1)*$this->k;
              //Set character spacing
              $this->_out(sprintf('BT %.2F Tc ET',$char_space));
          }
          //Override user alignment (since text will fill up cell)
          $align='';
      }

      //Pass on to Cell method
      $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);

      //Reset character spacing/horizontal scaling
      if ($fit)
          $this->_out('BT '.($scale ? '100 Tz' : '0 Tc').' ET');
    }

    //Cell with horizontal scaling only if necessary
    function CellFitScale($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,false);
    }

    //Cell with horizontal scaling always
    function CellFitScaleForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,true,true);
    }

    //Cell with character spacing only if necessary
    function CellFitSpace($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,false);
    }

    //Cell with character spacing always
    function CellFitSpaceForce($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        //Same as calling CellFit directly
        $this->CellFit($w,$h,$txt,$border,$ln,$align,$fill,$link,false,true);
    }
}
