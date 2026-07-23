# Ex6_CloudFlare

Offloads Magento 2 product and CMS image processing to [Cloudflare Image Resizing](https://developers.cloudflare.com/images/image-resizing/), rewriting image URLs so Cloudflare resizes, compresses, and reformats them on the fly instead of Magento's PHP image processor. It also adds responsive mobile `srcset`s to the product gallery for the PDP and PLP.

## What it does

- Rewrites catalog product image URLs (`Magento\Catalog\Model\View\Asset\Image`, `Magento\Catalog\Model\Product\Image`, `Magento\Catalog\Block\Product\ImageFactory`) to go through Cloudflare's `/cdn-cgi/image/` resizing endpoint instead of Magento's local image cache.
- Plugs into the WYSIWYG template filter (`Magento\Widget\Model\Template\Filter`) to optionally optimize images referenced in CMS content.
- Overrides the product gallery block (`Magento\Catalog\Block\Product\View\Gallery`) to attach optimized image URLs and, when enabled, a set of extra mobile image sizes (`srcset`) for the product details page and listing pages.
- Exposes per-image optimization parameters (compression, quality, format, fit) that are appended as Cloudflare resizing URL params.

## Requirements

- Magento 2 (Open Source/Commerce)
- A Cloudflare zone in front of the store with **Image Resizing** enabled (Cloudflare dashboard → Speed → Optimization → Image Resizing)

## Installation

```bash
composer require ex6/cloudflare
bin/magento module:enable Ex6_CloudFlare
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

Or, if installing manually, place the module at `app/code/Ex6/CloudFlare` and run the same `setup:upgrade` / `setup:di:compile` / `cache:flush` sequence.

## Configuration

Go to **Stores → Configuration → Ex6 → CloudFlare**.

### General

| Field   | Description |
|---------|-------------|
| API Key | Cloudflare API key (stored encrypted). |

### Image Optimization

| Field | Description |
|-------|-------------|
| Enable Image Optimization | Master switch for routing catalog images through Cloudflare. |
| CMS image optimization | Also apply optimization to images referenced in CMS/WYSIWYG content. |
| Compression | `None` or `Fast`. See [Cloudflare docs](https://developers.cloudflare.com/images/image-resizing/url-format/#compressionfast). |
| Format | Output format (only available when Compression is `None`). See [Cloudflare docs](https://developers.cloudflare.com/images/image-resizing/url-format/#format). |
| Fitment | Resize fit mode. See [Cloudflare docs](https://developers.cloudflare.com/images/image-resizing/url-format/#fit). |
| Quality | Output quality, `Original` or 50–95. See [Cloudflare docs](https://developers.cloudflare.com/images/image-resizing/url-format/#quality). |

### Mobile Images

| Field | Description |
|-------|-------------|
| Enable for PDP | Generate an extra set of resized images (`srcset`) for the product details page gallery. |
| Size list (PDP) | Comma-separated widths, e.g. `320,640,960,1280,2560`. |
| Enable for PLP | Generate an extra set of resized images for product listing pages. |
| Size list (PLP) | Comma-separated widths, e.g. `320,640,960,1280,2560`. |

All settings support default/website/store view scope, except the enable/disable toggles, which are default-scope only.

## Notes

- Compression, Format, Fitment, and Quality are scoped per website/store view, so different stores can apply different optimization settings.
- Setting a field to `None` / `Original` omits that parameter from the Cloudflare resizing URL, letting Cloudflare use its own defaults.
