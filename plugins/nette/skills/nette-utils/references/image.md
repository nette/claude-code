# Image

Class `Nette\Utils\Image` for image manipulation.

```php
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageType;
```

---

## Creating Images

```php
// Blank image (default black background)
$image = Image::fromBlank(100, 200);
$image = Image::fromBlank(100, 200, ImageColor::rgb(125, 0, 0));

// From file
$image = Image::fromFile('photo.jpg');
$image = Image::fromFile('photo.jpg', $type); // $type receives format

// From string
$image = Image::fromString($data);
```

---

## Saving Images

```php
// To file
$image->save('output.jpg');
$image->save('output.jpg', 80);  // JPEG quality 0-100 (default 85)
$image->save('output.png', 5);   // PNG compression 0-9 (default 9)
$image->save('output.tmp', null, ImageType::JPEG); // explicit format

// To string
$data = $image->toString(ImageType::JPEG, 80);

// To browser
$image->send(ImageType::PNG); // sends Content-Type header
```

### Quality Defaults
- JPEG: 85
- WEBP: 80
- AVIF: 30
- PNG: 9 (compression level)

---

## Formats

Constants: `ImageType::JPEG`, `ImageType::PNG`, `ImageType::GIF`, `ImageType::WEBP`, `ImageType::AVIF`, `ImageType::BMP`

```php
// Check support
Image::isTypeSupported(ImageType::WEBP); // bool
Image::getSupportedTypes(); // array

// Detect format
$type = Image::detectTypeFromFile('photo.jpg');
$type = Image::detectTypeFromString($data);

// Convert
Image::typeToExtension(ImageType::JPEG); // 'jpg'
Image::typeToMimeType(ImageType::JPEG);  // 'image/jpeg'
Image::extensionToType('jpg');           // ImageType::JPEG
```

---

## Resizing

```php
$image->resize(500, 300);      // max 500x300, keep aspect ratio
$image->resize(500, null);     // width 500px, height auto
$image->resize(null, 300);     // width auto, height 300px
$image->resize('75%', 300);    // 75% width, 300px height
```

### Resize Flags

```php
// Default: fit within bounds
$image->resize(500, 300, Image::OrSmaller);

// Fill area (may exceed in one dimension)
$image->resize(500, 300, Image::OrBigger);

// Fill and crop excess
$image->resize(500, 300, Image::Cover);

// Only shrink, never enlarge
$image->resize(500, 300, Image::ShrinkOnly);

// Ignore aspect ratio
$image->resize(500, 300, Image::Stretch);

// Combine flags
$image->resize(500, 300, Image::ShrinkOnly | Image::Stretch);
```

### Flipping

```php
$image->resize(null, '-100%');     // flip vertically
$image->resize('-100%', '-100%');  // rotate 180°
$image->resize(-125, 500);         // resize & flip horizontally
```

### Sharpening

```php
$image->sharpen(); // after resize for better quality
```

---

## Cropping

```php
$image->crop($left, $top, $width, $height);

// Percentages (like CSS background-position)
$image->crop('100%', '50%', '80%', '80%');

// Auto-crop (e.g., remove black borders)
$image->cropAuto(IMG_CROP_BLACK);
```

---

## Colors

```php
// RGB
$color = ImageColor::rgb(255, 0, 0);           // red
$color = ImageColor::rgb(0, 0, 255, 0.5);      // semi-transparent blue

// Hex
$color = ImageColor::hex('#F00');              // red
$color = ImageColor::hex('#00FF0080');         // semi-transparent green
$color = ImageColor::hex('#rgba');             // with alpha
```

---

## Drawing

```php
// Shapes
$image->ellipse($centerX, $centerY, $width, $height, $color);
$image->filledEllipse($centerX, $centerY, $width, $height, $color);
$image->rectangle($x1, $y1, $x2, $y2, $color);
$image->filledRectangle($x1, $y1, $x2, $y2, $color);
$image->rectangleWH($left, $top, $width, $height, $color);      // by dimensions
$image->filledRectangleWH($left, $top, $width, $height, $color);
$image->arc($centerX, $centerY, $width, $height, $start, $end, $color);
$image->polygon($points, $color);
$image->filledPolygon($points, $color);

// Lines and pixels
$image->line($x1, $y1, $x2, $y2, $color);
$image->setPixel($x, $y, $color);

// Fill
$image->fill($x, $y, $color);

// Text
$image->ttfText($size, $angle, $x, $y, $color, $fontFile, $text);
```

---

## Merging Images

```php
$logo = Image::fromFile('logo.png');
$blank = Image::fromBlank(320, 240, ImageColor::rgb(52, 132, 210));

// Place at position (pixels or percentages)
$blank->place($logo, '80%', '80%');        // bottom-right corner
$blank->place($logo, 10, 10);              // top-left at 10,10
$blank->place($logo, '50%', '50%', 25);    // centered, 25% opacity (watermark)
```

---

## Dimensions

```php
$width = $image->getWidth();
$height = $image->getHeight();
```

---

## Transformations

```php
// Rotate
$image->rotate($angle, $backgroundColor);

// Flip
$image->flip(IMG_FLIP_HORIZONTAL);
$image->flip(IMG_FLIP_VERTICAL);
$image->flip(IMG_FLIP_BOTH);

// Scale with interpolation
$image->scale($newWidth, $newHeight, IMG_BILINEAR_FIXED);

// Filters
$image->filter(IMG_FILTER_GRAYSCALE);
$image->filter(IMG_FILTER_BRIGHTNESS, 20);
$image->filter(IMG_FILTER_CONTRAST, -10);

// Gamma correction
$image->gammaCorrect($inputGamma, $outputGamma);
```

---

## Text Dimensions

Calculate text bounding box before drawing:

```php
$box = Image::calculateTextBox($text, $fontFile, $size, $angle);
// Returns: ['left', 'top', 'width', 'height']
```

---

## Alpha and Transparency

```php
// Enable alpha blending (default for truecolor)
$image->alphaBlending(true);

// Save full alpha channel in PNG
$image->saveAlpha(true);

// Set transparent color
$image->colorTransparent($color);
```
