<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

enum AdminValuesImportProblemType: string
{
    case InvalidHeader = 'invalidHeader';
    case InvalidRow = 'invalidRow';
    case AmbiguousName = 'ambiguousName';
    case NoMatch = 'noMatch';
    case UnknownSubcamp = 'unknownSubcamp';
    case DuplicateNameInCsv = 'duplicateNameInCsv';
    case DuplicateUniqueIdInCsv = 'duplicateUniqueIdInCsv';
    case UniqueIdTakenByOther = 'uniqueIdTakenByOther';
}
