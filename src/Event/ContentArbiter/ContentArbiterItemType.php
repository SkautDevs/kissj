<?php

declare(strict_types=1);

namespace kissj\Event\ContentArbiter;

enum ContentArbiterItemType: string
{
    case Text = 'text';
    case Email = 'email';
    case Phone = 'phone';
    case Date = 'date';
    case Select = 'select';
    case Textarea = 'textarea';
    case File = 'file';
    case Checkbox = 'checkbox';
    case TshirtComposite = 'tshirtComposite';
}
