<?php

declare(strict_types=1);

namespace kissj\Application\migrations\seeds\programmes;

class Navigamus3027Programmes
{
    /**
     * @var list<array{
     *     name: string,
     *     description: string,
     *     place: string,
     *     start: string,
     *     end: string,
     *     capacity: int,
     *     isPreregistered: bool,
     *     roles: list<string>,
     * }>
     */
    public const array PROGRAMMES = [
        [
            'name' => 'Plachtní regata',
            'description' => "Organizuje Olda Pešina.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Slapy – u kempu',
            'start' => '2027-06-03 14:00:00',
            'end' => '2027-06-03 18:00:00',
            'capacity' => 2,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Exkurze – elektrárna Štěchovice',
            'description' => "Doprava autobusem – cca 50–100 Kč.\nDélka prohlídky: 1,5 hod.\nKapacita: max 40 osob (20 v infocentru sleduje video, 20 je na prohlídce).\nProhlídka je zdarma.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Elektrárna Štěchovice',
            'start' => '2027-06-03 13:30:00',
            'end' => '2027-06-03 16:30:00',
            'capacity' => 5,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Plavba výletní lodí po Slapech',
            'description' => "Z kempu na hráz a zpět cca 1,5 hod. + 30 min. procházka po hrázi.\nKapacita lodi: 150 míst (80 k sezení), při velkém zájmu lze objednat dvě lodě.\nCena podle počtu zájemců – cca 100–150 Kč.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Přístaviště u kempu',
            'start' => '2027-06-03 14:00:00',
            'end' => '2027-06-03 16:00:00',
            'capacity' => 30,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Prohlídka zámku Konopiště',
            'description' => "Doprava autobusem – cca 50–100 Kč.\na) 2. okruh (Salony severního křídla) – 55 min., dospělí 280 Kč, mládež 220 Kč, děti 80 Kč.\nb) Muzeum sv. Jiří (patron skautů) – 30 min., 80 Kč bez rozdílu věku.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Zámek Konopiště',
            'start' => '2027-06-03 13:00:00',
            'end' => '2027-06-03 17:30:00',
            'capacity' => 2,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Výlet na Drtinovu rozhlednu nebo rozhlednu Veselý vrch',
            'description' => "Pramicí na druhý břeh, pak pěšky cca 4 km tam a zpět.\nObě rozhledny jsou volně přístupné.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Rozhledny za přehradou',
            'start' => '2027-06-03 14:00:00',
            'end' => '2027-06-03 18:00:00',
            'capacity' => 5,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Program vodní záchranné služby',
            'description' => "Modelové ukázky – záchrana tonoucího, vytažení na loď s transportem na břeh, využití paddleboardu (15 min.).\nPráce s házečkami, resuscitace na figuríně, první pomoc (2 skupiny, max 20 osob, 30 min.).\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Kemp',
            'start' => '2027-06-03 14:00:00',
            'end' => '2027-06-03 15:15:00',
            'capacity' => 30,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Plavba k místu prvního tábora skautek',
            'description' => "Na vlastních lodích cca 2 km na dnes zatopené místo, kde v roce 1915 tábořily první skautky.\nVyprávění a dobové fotografie: skautský historik Roman Šantora – Bobo.\nDélka cca 1,5 hod., vhodné pro děti starší 10 let.\nKapacita: max 30 osob.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Přístaviště u kempu',
            'start' => '2027-06-03 14:00:00',
            'end' => '2027-06-03 15:30:00',
            'capacity' => 2,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
        [
            'name' => 'Plavba k místu prvního tábora skautek',
            'description' => "Druhá plavba, pokud je zájem.\nNa vlastních lodích cca 2 km na dnes zatopené místo, kde v roce 1915 tábořily první skautky.\nVyprávění a dobové fotografie: skautský historik Roman Šantora – Bobo.\nDélka cca 1,5 hod., vhodné pro děti starší 10 let.\nKapacita: max 30 osob.\nCeny doprovodných programů nejsou zahrnuty v registračním poplatku.",
            'place' => 'Přístaviště u kempu',
            'start' => '2027-06-03 16:00:00',
            'end' => '2027-06-03 17:30:00',
            'capacity' => 5,
            'isPreregistered' => false,
            'roles' => ['ist'],
        ],
    ];
}
