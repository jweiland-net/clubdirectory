# TYPO3 Extension `clubdirectory`

![Build Status](https://github.com/jweiland-net/clubdirectory/workflows/CI/badge.svg)

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
