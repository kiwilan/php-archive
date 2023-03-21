# Metadata for the media tests

## Archives

```bash
rm -f archive.zip archive.7z archive.tar archive.tar.gz archive.tar.bz2 archive.tar.xz archive.rar
```

```bash
zip archive.zip ./archive/*
7z a archive.7z ./archive
tar -tvf archive.tar ./archive
tar -czvf archive.tar.gz ./archive
tar -cjvf archive.tar.bz2 ./archive
tar -cJf archive.tar.xz ./archive
rar a archive.rar ./archive
```

## PDF

```bash
brew install exiftool
```

```bash
exiftool \
  -Author="Vue Mastery" \
  -CreationDate="2019:10:18 00:00:00" \
  -Creator="Vue Mastery PDF" \
  -Keywords="Vue3,Vue,composition-api" \
  -Producer="Vue" \
  -Subject="Vue3 Composition API" \
  -Title="Vue3 Composition API" \
  "./example.pdf"
```

```bash
exiftool -a -G1 example.pdf
```
