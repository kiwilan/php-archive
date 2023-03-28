# SevenZipProcess

## List

tar extended:

```bash
7z x tests/media/archive.tar.gz -so | 7z x -aoa -si -ttar -otemp
7z x tests/media/archive.tar.gz -so | 7z l -ba -si -ttar -otemp

rm -rf temp ; mkdir temp ; 7z x tests/media/archive.tar.gz -otemp ; 7z l -ba -slt temp ; rm -rf temp
tar -tf tests/media/archive.tar.gz
```

```php
if ($this->type === ArchiveEnum::tarExtended) {
    $this->recurseRmdir($this->outputDir);
    mkdir($this->outputDir);
    $this->process('7z', ['x', '-y', $this->path, '-otemp']);
    $output = $this->process('7z', ['l', '-ba', '-slt', $this->outputDir]);
    $this->recurseRmdir($this->outputDir);
}
```

## Extract

```bash
# Extract specific file
7z e tests/media/archive.7z -otests/output file-1.md -r
#  Extract all files
rm -rf tests/output ; 7z x tests/media/archive.7z -otests/output -r
```

```php
if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
    // rar x -y tests/media/archive.rar temp/
    $output = $this->process('rar', ['x', '-y', $this->path, $path]);
} else {
    // 7z x -y tests/media/archive.tar.gz -otemp
    if ($this->type === ArchiveEnum::tarExtended) {
        $this->process('7z', ['x', '-y', $this->path, '-otemp']);
        $this->process('7z', ['x', '-y', $this->outputDir, "-o{$path}"]);
    }
}
```

## Content

```bash
rm -rf temp ;  x -y -otemp tests/media/archive.tar archive/cover.jpeg

rm -rf temp ; unrar x -y tests/media/archive.rar archive/cover.jpeg temp/
rm -rf temp ; rar x -y tests/media/archive.rar archive/cover.jpeg temp/
```

```bash
if ($this->type !== ArchiveEnum::tar && $this->type !== ArchiveEnum::tarExtended) {
    $args[] = $extract;
}

if ($this->type === ArchiveEnum::rar && $this->isDarwin) {
    $command = 'rar';
    $args = ['x', '-y', $this->path, $extract, $this->outputDir];
}
$this->execute('7z', ['x', '-y', "-o{$this->outputDir}", $this->path]);
```
