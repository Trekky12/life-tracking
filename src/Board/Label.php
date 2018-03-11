<?php

namespace App\Board;

class Label extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->changedOn = $this->exists('changedOn', $data) ? $data['changedOn'] : date('Y-m-d G:i:s');
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->board = $this->exists('board', $data) ? filter_var($data['board'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->color = $this->exists('color', $data) ? filter_var($data['color'], FILTER_SANITIZE_STRING) : null;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!preg_match("/^#[a-f0-9]{6}$/i", $this->color)) {
            $this->parsing_errors[] = "WRONG_COLOR_TYPE";
        }
    }

    public function getTextColor() {
        if (!is_null($this->color)) {
            //return $this->getTextColorLumDiff();
            return $this->getTextColorYIQ();
        }
        return '#000000';
    }
    

    /**
     * @see https://24ways.org/2010/calculating-color-contrast
     */
    private function getTextColorYIQ() {
        list($r, $g, $b) = $this->getRGB($this->color);
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        return ($yiq >= 128) ? '#000000' : '#FFFFFF';
    }

    /**
     * @see https://www.splitbrain.org/blog/2008-09/18-calculating_color_contrast_with_php
     */
    private function getTextColorLumDiff() {
        list($r, $g, $b) = $this->getRGB($this->color);
        list($rb, $gb, $bb) = $this->getRGB('#000000');
        list($rw, $gw, $bw) = $this->getRGB('#FFFFFF');
        
        $diff_black = $this->calcLumDiff($r, $g, $b, $rb, $gb, $bb);
        $diff_white = $this->calcLumDiff($r, $g, $b, $rw, $gw, $bw);
        
        return $diff_black > $diff_white ? '#000000' : '#FFFFFF';
    }

    private function calcLumDiff($R1, $G1, $B1, $R2, $G2, $B2) {
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
                0.7152 * pow($G1 / 255, 2.2) +
                0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2 / 255, 2.2) +
                0.7152 * pow($G2 / 255, 2.2) +
                0.0722 * pow($B2 / 255, 2.2);

        if ($L1 > $L2) {
            return ($L1 + 0.05) / ($L2 + 0.05);
        } else {
            return ($L2 + 0.05) / ($L1 + 0.05);
        }
    }

    private function getRGB($color) {
        if (!is_null($color)) {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
            return array($r, $g, $b);
        }
        return null;
    }

}
