<?php
/**
 * Класс для экспорта данных в PDF и Word форматы
 */

require_once __DIR__ . '/autoload.php';

class ExportHelper {
    
    /**
     * Экспорт реестра мероприятий в PDF
     */
    public static function exportRegistryPDF($events, $filename = 'Reestr_meropriyatiy.pdf') {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Настройки документа
        $pdf->SetCreator('СГУВТ');
        $pdf->SetAuthor('СГУВТ');
        $pdf->SetTitle('Реестр мероприятий');
        
        // Убираем header и footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Устанавливаем шрифт
        $pdf->SetFont('dejavusans', '', 10);
        
        // Добавляем страницу
        $pdf->AddPage('L');
        
        // Заголовок
        $html = '<h1 style="text-align: center; font-size: 16px;">Реестр мероприятий СГУВТ</h1>';
        
        // Таблица
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; font-size: 9px;">';
        $html .= '<thead repeat="true"><tr style="background-color: #529c81; color: white;">';
        $html .= '<th style="width: 8%;">Дата</th>';
        $html .= '<th style="width: 10%;">Институт</th>';
        $html .= '<th style="width: 12%;">Факультет</th>';
        $html .= '<th style="width: 15%;">Тип и Название</th>';
        $html .= '<th style="width: 15%;">Организатор</th>';
        $html .= '<th style="width: 25%;">Описание</th>';
        $html .= '<th style="width: 15%;">Участники</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($events as $e) {
            $html .= '<tr nobr="true">';
            $html .= '<td style="width: 8%;">' . date('d.m.Y', strtotime($e['date_start'])) . '</td>';
            $html .= '<td style="width: 10%;"><b>' . htmlspecialchars($e['short_name']) . '</b></td>';
            $html .= '<td style="width: 12%;">' . htmlspecialchars($e['faculty_name'] ?? '—') . '</td>';
            $html .= '<td style="width: 15%;"><span style="color: #529c81; font-size: 8px;">' . htmlspecialchars($e['category_name']) . '</span><br>' . htmlspecialchars($e['title']) . '</td>';
            $html .= '<td style="width: 15%;"><b>' . htmlspecialchars($e['organizer']) . '</b><br><i>' . htmlspecialchars($e['location']) . '</i></td>';
            $html .= '<td style="width: 25%;">' . nl2br(htmlspecialchars($e['description'] ?: 'ПУСТО')) . '</td>';
            $html .= '<td style="width: 15%;"><b>Всего: ' . $e['parts_count'] . '</b><br>' . htmlspecialchars($e['parts_list'] ?: 'Нет данных') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Вывод
        $pdf->Output($filename, 'D');
    }
    
    /**
     * Экспорт отчета в PDF
     */
    public static function exportReportPDF($records, $date_from, $date_to, $metrics, $filename = 'Otchet_SGUVT.pdf') {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        $pdf->SetCreator('СГУВТ');
        $pdf->SetAuthor('СГУВТ');
        $pdf->SetTitle('Отчет по мероприятиям');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage('L');
        
        // Заголовок
        $html = '<h1 style="text-align: center; font-size: 16px;">Отчетность по мероприятиям СГУВТ</h1>';
        $html .= '<p style="text-align: center; color: #666;">Период: с ' . htmlspecialchars($date_from) . ' по ' . htmlspecialchars($date_to) . '</p>';
        
        // Метрики
        $html .= '<table cellpadding="5" style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr>';
        $html .= '<td style="text-align: center; border: 1px solid #ddd; padding: 15px;"><b style="font-size: 18px; color: #529c81;">' . (int)$metrics['ev_count'] . '</b><br>Мероприятий</td>';
        $html .= '<td style="text-align: center; border: 1px solid #ddd; padding: 15px;"><b style="font-size: 18px; color: #529c81;">' . (int)$metrics['winners_count'] . '</b><br>Призовых мест</td>';
        $html .= '<td style="text-align: center; border: 1px solid #ddd; padding: 15px;"><b style="font-size: 18px; color: #529c81;">' . (int)$metrics['awards_count'] . '</b><br>Выдано наград</td>';
        $html .= '</tr></table>';
        
        // Отступ перед таблицей данных
        $html .= '<br><br>';
        
        // Таблица данных
        $html .= '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%; font-size: 10px;">';
        $html .= '<thead repeat="true"><tr style="background-color: #529c81; color: white;">';
        $html .= '<th style="width: 10%;">Дата</th>';
        $html .= '<th style="width: 12%;">Институт</th>';
        $html .= '<th style="width: 12%;">Факультет</th>';
        $html .= '<th style="width: 25%;">Название</th>';
        $html .= '<th style="width: 20%;">ФИО / Группа</th>';
        $html .= '<th style="width: 21%;">Награды</th>';
        $html .= '</tr></thead><tbody>';
        
        foreach ($records as $r) {
            $html .= '<tr nobr="true">';
            $html .= '<td style="width: 10%;">' . date('d.m.Y', strtotime($r['date_start'])) . '</td>';
            $html .= '<td style="width: 12%;"><b>' . htmlspecialchars($r['short_name']) . '</b></td>';
            $html .= '<td style="width: 12%;">' . htmlspecialchars($r['faculty_name'] ?? '—') . '</td>';
            $html .= '<td style="width: 25%;"><span style="color: #529c81; font-size: 9px;">' . htmlspecialchars($r['cat_name']) . '</span><br>' . htmlspecialchars($r['title']) . '</td>';
            $html .= '<td style="width: 20%;"><b>' . htmlspecialchars($r['participant_info'] ?: '—') . '</b><br>' . htmlspecialchars($r['role_in_event']) . '</td>';
            
            $award = trim($r['award_details'] ?? '');
            if (!empty($award) && $award !== 'ПУСТО') {
                $html .= '<td style="width: 21%;"><b>' . htmlspecialchars($award) . '</b></td>';
            } else {
                $html .= '<td style="width: 21%; color: #777;">Награды отсутствуют</td>';
            }
            
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        
        $pdf->writeHTML($html, true, false, false, false, '');
        $pdf->Output($filename, 'D');
    }
    
    /**
     * Экспорт реестра в Word
     */
    public static function exportRegistryWord($events, $filename = 'Reestr_meropriyatiy.docx') {
        // Подавляем deprecated warnings от PHPWord (библиотека использует устаревшие функции)
        // Это необходимо для корректной работы экспорта без вывода предупреждений пользователю
        $oldErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Настройки стиля - альбомная ориентация
        $section = $phpWord->addSection([
            'defaultFontName' => 'Times New Roman',
            'defaultFontSize' => 11,
            'orientation' => \PhpOffice\PhpWord\Style\Section::ORIENTATION_LANDSCAPE,
            'pageSizeW' => 16838, // A4 landscape width in twips
            'pageSizeH' => 11906, // A4 landscape height in twips
        ]);
        
        // Заголовок
        $section->addText('Реестр мероприятий СГУВТ', [
            'bold' => true,
            'size' => 16,
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $section->addTextBreak(1);
        
        // Таблица
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '#000000',
            'cellMargin' => 50,
        ]);
        
        // Заголовки с background
        $headerRow = $table->addRow();
        $headerRow->addCell(1000, ['bgColor' => '529C81'])->addText('Дата', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(1200, ['bgColor' => '529C81'])->addText('Институт', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(1400, ['bgColor' => '529C81'])->addText('Факультет', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(2000, ['bgColor' => '529C81'])->addText('Тип и Название', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(2000, ['bgColor' => '529C81'])->addText('Организатор', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(3000, ['bgColor' => '529C81'])->addText('Описание', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(2000, ['bgColor' => '529C81'])->addText('Участники', ['bold' => true, 'color' => 'FFFFFF']);
        
        // Данные
        foreach ($events as $e) {
            $row = $table->addRow();
            $row->addCell(1000)->addText(date('d.m.Y', strtotime($e['date_start'])));
            $row->addCell(1200)->addText($e['short_name'] ?? '', ['bold' => true]);
            $row->addCell(1400)->addText($e['faculty_name'] ?? '—');
            
            $cell = $row->addCell(2000);
            $cell->addText($e['category_name'] ?? '', ['color' => '529c81', 'size' => 9]);
            $cell->addText($e['title'] ?? '');
            
            $cell = $row->addCell(2000);
            $cell->addText($e['organizer'] ?? '', ['bold' => true]);
            $cell->addText($e['location'] ?? '', ['italic' => true, 'size' => 9]);
            
            $row->addCell(3000)->addText($e['description'] ?: 'ПУСТО');
            
            $cell = $row->addCell(2000);
            $cell->addText('Всего: ' . $e['parts_count'], ['bold' => true]);
            $cell->addText($e['parts_list'] ?: 'Нет данных', ['size' => 9]);
        }
        
        // Сохранение
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        $objWriter->save("php://output");
        error_reporting($oldErrorReporting); // Восстанавливаем предыдущий уровень
        exit;
    }
    
    /**
     * Экспорт отчета в Word
     */
    public static function exportReportWord($records, $date_from, $date_to, $metrics, $filename = 'Otchet_SGUVT.docx') {
        // Подавляем deprecated warnings от PHPWord (библиотека использует устаревшие функции)
        // Это необходимо для корректной работы экспорта без вывода предупреждений пользователю
        $oldErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Альбомная ориентация
        $section = $phpWord->addSection([
            'defaultFontName' => 'Times New Roman',
            'defaultFontSize' => 11,
            'orientation' => \PhpOffice\PhpWord\Style\Section::ORIENTATION_LANDSCAPE,
            'pageSizeW' => 16838,
            'pageSizeH' => 11906,
        ]);
        
        // Заголовок
        $section->addText('Отчетность по мероприятиям СГУВТ', [
            'bold' => true,
            'size' => 16,
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $section->addText('Период: с ' . $date_from . ' по ' . $date_to, [
            'color' => '666666',
        ], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $section->addTextBreak(1);
        
        // Метрики - таблица на всю ширину с равными ячейками, центрированная
        $metricsTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '#DDDDDD',
            'width' => 12600,
            'unit' => 'dxa',
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
        ]);
        $metricsRow = $metricsTable->addRow();
        // Ширина ячеек в twips (12600 / 3 = 4200)
        $metricsRow->addCell(4200, ['valign' => 'center'])->addText((int)$metrics['ev_count'] . "\nМероприятий", ['bold' => true, 'size' => 14, 'color' => '529c81'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $metricsRow->addCell(4200, ['valign' => 'center'])->addText((int)$metrics['winners_count'] . "\nПризовых мест", ['bold' => true, 'size' => 14, 'color' => '529c81'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $metricsRow->addCell(4200, ['valign' => 'center'])->addText((int)$metrics['awards_count'] . "\nВыдано наград", ['bold' => true, 'size' => 14, 'color' => '529c81'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        
        $section->addTextBreak(1);
        
        // Таблица данных - центрированная, как таблица метрик
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '#000000',
            'cellMargin' => 50,
            'width' => 12600,
            'unit' => 'dxa',
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
        ]);
        
        // Заголовки с background
        $headerRow = $table->addRow();
        $headerRow->addCell(1200, ['bgColor' => '529C81'])->addText('Дата', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(1500, ['bgColor' => '529C81'])->addText('Институт', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(1500, ['bgColor' => '529C81'])->addText('Факультет', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(3000, ['bgColor' => '529C81'])->addText('Название', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(2500, ['bgColor' => '529C81'])->addText('ФИО / Группа', ['bold' => true, 'color' => 'FFFFFF']);
        $headerRow->addCell(2500, ['bgColor' => '529C81'])->addText('Награды', ['bold' => true, 'color' => 'FFFFFF']);
        
        // Данные
        foreach ($records as $r) {
            $row = $table->addRow();
            $row->addCell(1200)->addText(date('d.m.Y', strtotime($r['date_start'])));
            $row->addCell(1500)->addText($r['short_name'] ?? '', ['bold' => true]);
            $row->addCell(1500)->addText($r['faculty_name'] ?? '—');
            
            $cell = $row->addCell(3000);
            $cell->addText($r['cat_name'] ?? '', ['color' => '529c81', 'size' => 9]);
            $cell->addText($r['title'] ?? '');
            
            $cell = $row->addCell(2500);
            $cell->addText($r['participant_info'] ?: '—', ['bold' => true]);
            $cell->addText($r['role_in_event'] ?? '', ['size' => 9]);
            
            $award = trim($r['award_details'] ?? '');
            if (!empty($award) && $award !== 'ПУСТО') {
                $row->addCell(2500)->addText($award, ['bold' => true]);
            } else {
                $row->addCell(2500)->addText('Награды отсутствуют', ['color' => '777777', 'size' => 9]);
            }
        }
        
        // Сохранение
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: max-age=0");
        $objWriter->save("php://output");
        error_reporting($oldErrorReporting); // Восстанавливаем предыдущий уровень
        exit;
    }
}
