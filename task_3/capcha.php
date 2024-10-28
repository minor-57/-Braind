<?php
        // я не нашел нормальную капчу с треугольником, но круг и квадрат оно определяет
        // скрипт считывает файл в массив, определяет центр и конур фигуры, после чего считает углы фигуры
    function toInt($val){
        return (int)$val;
    }

    function getСontour($maxX, $maxY, $arr){ //для определения контура фигуры я использовал алгоритм трассировки окрестностей Мура
        $stopFlag = false;
        $countur = array();
        $initialPoint = array('x' => 0, 'y' => 0); // координаты точки, с которой начали обход
        $currentPoint = array('x' => 0, 'y' => 0);  // текущая точка
        $lastPoint = array('x' => 0, 'y' => 0); // предыдущая точка
        $directions = [ // задаем шаги для обхода окресности точки
            [-1, 0], // вверх
            [-1, 1], // вверх-вправо
            [0, 1],  // вправо
            [1, 1],  // вниз-вправо
            [1, 0],  // вниз
            [1, -1], // вниз-влево
            [0, -1], // влево
            [-1, -1], // вверх-влево
        ];
        for ($i = 0; $i < $maxY; $i++){     //ищем первую точку фигуры
            for ($j = 0; $j < $maxX; $j++){
                if ($arr[$i][$j] == '1'){
                    array_push($countur, ['x' => $j, 'y' => $i]);
                    $initialPoint = array('x' => $j, 'y' => $i);
                    if ($i == 0 && $j == 0){
                        continue;
                    } elseif ($j == 0){
                        $lastPoint = array('x' => $maxX - 1, 'y' => $i); // записываем координаты точки
                    } else{
                        $lastPoint = array('x' => $j - 1, 'y' => $i);   //  записываем координаты точки, из которой попали в текущую
                    }
                    $stopFlag = true;
                    break;
                }
            }
            if ($stopFlag) break;
        }
        $currentPoint = array('x' => $initialPoint['x'], 'y' => $initialPoint['y']);
        do {
            $dirIndex = 0; // разность координат прошлой и текущей точек
            for($j = 0; $j < 8; $j++){
                if($directions[$j][1] == $lastPoint['x'] - $currentPoint['x'] && $directions[$j][0] == $lastPoint['y'] - $currentPoint['y']){
                    $dirIndex = $j;
                    break;
                }
            }
            for($i = 0; $i < 8; $i++){
                $dir = ($dirIndex + $i) % 8;    // обход области начинается с точки, из которой мы попали в текущую
                $newX = $currentPoint['x'] + $directions[$dir][1];
                $newY = $currentPoint['y'] + $directions[$dir][0];
                
                if(isset($arr[$newY][$newX]) && $arr[$newY][$newX] == '1'){
                    $currentPoint['x'] = $newX;
                    $currentPoint['y'] = $newY;
                    array_push($countur, ['x' => $newX, 'y' => $newY]);
                    break;
                } elseif (isset($directions[$dir - 1])){
                    $lastPoint['x'] = $newX;
                    $lastPoint['y'] = $newY;
                }
            }
        } while ($currentPoint['x'] !== $initialPoint['x'] || $currentPoint['y'] !== $initialPoint['y']);
        array_pop($countur); // т.к. дважды считывается начальная точка, выкидываем ее :)
        return $countur;

    }
    function findCenter($maxX, $maxY, $arr){ // считаем координаты центра как среднее арифметическое коодинат точек фигуры
        $xSumm = 0;
        $ySumm = 0;
        $count = 0;

        for ($i = 0; $i < $maxY; $i++){
            for ($j = 0; $j < $maxX; $j++){
                if ($arr[$i][$j] == '1'){
                    $xSumm += $j;
                    $ySumm += $i;
                    ++$count;
                }
            }
        }
        return array('x' => toInt($xSumm / $count), 'y' => toInt($ySumm / $count));
    }

    function calcDistanses($center, $maxX, $maxY, $arr){
        $distanses = array();
        foreach($arr as $point){
            $deltaX = $point['x'] - $center['x'];
            $deltaY = $point['y'] - $center['y'];
            $dist = pow($deltaX, 2) + pow($deltaY, 2); // теорема пифагора
            $angle = atan2($deltaY, $deltaX);  //арктангенс
            array_push($distanses, [$dist, $angle]); 
        }
    
        usort($distanses, function($a, $b){ // сортируем расстояния по значению угла относительно центра
            return $a[1] <=> $b[1];
        });
        return $distanses;
    }

    function findPeaks($arr){ // ищем пики в последовательности расстояний, это и будут углы (самые удаленные от центра точки)
        $peacks = 0;
        $size = sizeof($arr);

        for($i = 1; $i < $size - 1; $i++){
            if($arr[$i -1 ][0] < $arr[$i][0] && $arr[$i][0] > $arr[$i + 1][0]){
                ++$peacks;
            }
        }
        return $peacks;
    }

    function solveCapcha($file){
        $input = fopen( $file, 'r');
        [$maxX, $maxY] = array_map('toInt', explode( ' ',trim(fgets($input)))); //считываем размер картинки (предполанается что первое число - ширина)
        $bits = array(array());
        $center = array();
        $peaks = 0;

        for ($i = 0; $i < $maxY; $i++){
            $bits[$i] = explode( ' ',trim(fgets($input))); // считываем капчу в массив
        }
        
        $countur = getСontour($maxX, $maxY, $bits); //формируем массив из элементов, составляющих контур фигуры
        $center = findCenter($maxX, $maxY, $bits);  // ищем координаты центра
        $distanses = calcDistanses($center, $maxX, $maxY, $countur); // считаем расстояния  и значения углов точек контура относительно центра
        $peaks = findPeaks($distanses); //считаем углы фигуры
        if($peaks == 3){
            return 'triangle';
        } elseif ($peaks == 4) {
            return 'square';
        } elseif ($peaks > 4) {
            return "circle";
        } else {
            return 'i don`t know :(';
        };
    }
   
    echo solveCapcha('circle.txt');