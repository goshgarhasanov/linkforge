<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\LinkService;
use App\Support\Exceptions\HttpException;
use App\Support\Http\JsonResponder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Margin\Margin;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

final class QrController
{
    public function __construct(
        private readonly LinkService $links,
    ) {
    }

    public function png(ServerRequestInterface $request, array $args): ResponseInterface
    {
        return $this->render($args['code'], 'png');
    }

    public function svg(ServerRequestInterface $request, array $args): ResponseInterface
    {
        return $this->render($args['code'], 'svg');
    }

    private function render(string $code, string $format): ResponseInterface
    {
        try {
            $link = $this->links->findByCode($code);
        } catch (HttpException $e) {
            return JsonResponder::error($e->getMessage(), $e->getCode());
        }

        $shortUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080', '/') . '/' . $link->short_code;

        $qr = new QrCode(
            data: $shortUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 16,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(15, 23, 42),
            backgroundColor: new Color(255, 255, 255),
        );

        $writer = $format === 'svg' ? new SvgWriter() : new PngWriter();
        $result = $writer->write($qr);

        $response = (new Response())->withHeader('Content-Type', $result->getMimeType());

        if ($format === 'png') {
            $response = $response->withHeader('Content-Disposition', 'inline; filename="linkforge-' . $code . '.png"');
        }

        $response->getBody()->write($result->getString());

        return $response;
    }
}
