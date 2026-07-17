<?php

/** Affiche une note sous forme d'etoiles avec alternative textuelle (RGAA). */
function renderStarRating(int $note, int $max = 5): string
{
    $note = max(0, min($max, $note));
    $html = '<span class="star-rating" role="img" aria-label="Note : ' . $note . ' sur ' . $max . '">';
    for ($i = 1; $i <= $max; $i++) {
        $class = $i <= $note ? 'star-filled' : 'star-empty';
        $html .= '<span class="star-rating-char ' . $class . '" aria-hidden="true">★</span>';
    }
    $html .= '</span>';
    return $html;
}

/**
 * Tableau accessible en complement d'un graphique (canvas).
 *
 * @param array<int, array<int, string|int|float>> $rows
 */
function renderChartDataTable(string $caption, array $headers, array $rows, string $tableId): string
{
    $html = '<div class="chart-data-fallback mt-3">';
    $html .= '<details class="chart-details">';
    $html .= '<summary>Voir les donnees du graphique en tableau</summary>';
    $html .= '<div class="table-responsive mt-2">';
    $html .= '<table class="table table-sm table-bordered mb-0" id="' . htmlspecialchars($tableId) . '">';
    $html .= '<caption class="visually-hidden">' . htmlspecialchars($caption) . '</caption>';
    $html .= '<thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th scope="col">' . htmlspecialchars((string)$header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
        }
        $html .= '</tr>';
    }
    if (empty($rows)) {
        $html .= '<tr><td colspan="' . count($headers) . '">Aucune donnee</td></tr>';
    }
    $html .= '</tbody></table></div></details></div>';
    return $html;
}

/** Attributs figure pour un conteneur de graphique Chart.js. */
function chartFigureAttrs(string $title, string $describedBy): string
{
    return 'role="figure" aria-label="' . htmlspecialchars($title) . '" aria-describedby="' . htmlspecialchars($describedBy) . '"';
}
