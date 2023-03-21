# Metadata for the media tests

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
