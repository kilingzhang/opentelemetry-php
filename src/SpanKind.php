<?php

namespace Kilingzhang\OpenTelemetry;

class SpanKind
{
    const kUnspecified = 0;
    const kInternal = 1;
    const kServer = 2;
    const kClient = 3;
    const kProducer = 4;
    const kConsumer = 5;
}