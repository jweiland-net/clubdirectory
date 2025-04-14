# TYPO3 Extension `clubdirectory`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-shield]][TYPO3-13-url]

![Build Status][extension-ci-shield]

With `clubdirectory` you can create, manage and display club entries.

## 1 Features

* Create and manage clubs

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/clubdirectory
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `clubdirectory` with the extension manager module.

### 2.2 Minimal setup

1) Include the static TypoScript of the extension.
2) Create club records on a sysfolder.
3) Add clubdirectory plugin on a page and select at least the sysfolder as startingpoint.


<!-- MARKDOWN LINKS & IMAGES -->

[extension-build-shield]: https://poser.pugx.org/jweiland/clubdirectory/v/stable.svg?style=for-the-badge

[extension-ci-shield]: https://github.com/jweiland-net/clubdirectory/actions/workflows/ci.yml/badge.svg

[extension-downloads-badge]: https://poser.pugx.org/jweiland/clubdirectory/d/total.svg?style=for-the-badge

[extension-monthly-downloads]: https://poser.pugx.org/jweiland/clubdirectory/d/monthly?style=for-the-badge

[extension-ter-url]: https://extensions.typo3.org/extension/clubdirectory/

[extension-packagist-url]: https://packagist.org/packages/jweiland/clubdirectory/

[packagist-logo-stable]: https://img.shields.io/badge/--grey.svg?style=for-the-badge&logo=packagist&logoColor=white

[TYPO3-13-url]: https://get.typo3.org/version/13

[TYPO3-shield]: https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge&logo=typo3
