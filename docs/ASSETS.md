# Visual Assets (M2)

Status: partial. Canva returned `quota_exceeded` ("User has exceeded their credit quota") partway through the hero batch, so only priority 1 (hero banners) was delivered. All other sets are listed under Missing Assets.

Canva folder: [PLAYVAULT Assets](https://www.canva.com/folder/FAHad74IjHc)

## Delivered files

Paths are relative to `backend/resources/assets/images/`. All files are textless WebP, encoded at quality 84.

| Path | Dimensions | Size | Placement | Origin | Licence note | Canva design |
|---|---|---|---|---|---|---|
| `hero/hero-01-keycut-desktop-2400x1000.webp` | 2400x1000 | 61 KB | Homepage hero, concept 1 (key-cut slabs), desktop | Canva AI generation (create-design), cropped | Licence confirmation needed, see below | [DAHad-N3Sdo](https://canva.link/der2j81xgc99m31) |
| `hero/hero-01-keycut-mobile-1080x1350.webp` | 1080x1350 | 52 KB | Homepage hero, concept 1, mobile | Crop of the concept 1 desktop export | Licence confirmation needed | [DAHad-N3Sdo](https://canva.link/der2j81xgc99m31) |
| `hero/hero-02-vault-dial-desktop-2400x1000.webp` | 2400x1000 | 82 KB | Homepage hero, concept 2 (vault dial), desktop | Canva AI generation (create-design), retouched, cropped | Licence confirmation needed | [DAHadzik5Gw](https://canva.link/43tbb0pwm77kf9b) |
| `hero/hero-02-vault-dial-mobile-1080x1350.webp` | 1080x1350 | 89 KB | Homepage hero, concept 2, mobile | Crop of the retouched concept 2 desktop export | Licence confirmation needed | [DAHadzik5Gw](https://canva.link/43tbb0pwm77kf9b) |
| `hero/hero-03-glass-cards-desktop-2400x1000.webp` | 2400x1000 | 66 KB | Homepage hero, concept 3 (glass cards), desktop | Canva AI generation (create-design), cropped | Licence confirmation needed | [DAHad800P5U](https://canva.link/kwgm4jjx54qz3i9) |
| `hero/hero-03-glass-cards-mobile-1080x1350.webp` | 1080x1350 | 73 KB | Homepage hero, concept 3, mobile | Crop of the concept 3 desktop export | Licence confirmation needed | [DAHad800P5U](https://canva.link/kwgm4jjx54qz3i9) |

### Production notes

- Concept 2 desktop was retouched after export: a stray smudge above and left of the dial and jagged shapes along the floor were faded into the background. The source design in Canva still contains them.
- Mobile files are crops of the desktop exports, because the two dedicated portrait designs could not be exported (see Missing Assets). They have no calm area for overlaid copy, so the storefront needs a scrim or an art-on-top layout as in the Figma mobile frame.
- Canva AI output is soft at 1:1 on close inspection, most visibly on concept 3. Acceptable at hero scale.
- All three concepts carry no baked-in text, so they work for English and Arabic without a second version.
- Concept 2 is an original vault dial following the Figma hero art. No prompt named a brand, and no brand logos, characters, controllers or consoles appear.

### Licence confirmation before commercial use

Every delivered file is Canva AI output; no Canva library element was used. Before launch, confirm that the Canva plan on this account permits commercial use of AI-generated output and check Canva's current terms on AI content, including ownership and non-exclusivity. Comparable images may be generated for other users.

### Rejected or unused in Canva

These remain in the folder and are not in the project.

| Item | Reason |
|---|---|
| [DAHad3QnC8A](https://www.canva.com/d/BTz9FIZ4QoXH_Ls), concept 1 resized to 1080x1350 | Rejected. Canva's resize duplicated the slab composition awkwardly. |
| `MAHad6XC-zM`, generated test image for concept 1 | Unused. Superseded by the concept 1 design. |
| [DAHad4QmPfA](https://canva.link/n9vm2q7b8b3jfge), concept 1 portrait | Export refused, see below. |
| [DAHad_yXE2Q](https://canva.link/v7az58rmcnhjq1m), concept 2 portrait | Export refused, see below. |

## Missing Assets

| Priority | Asset | Status |
|---|---|---|
| 1 | Dedicated portrait hero art for concepts 1 and 2 | Designs exist in Canva, but export failed with "Not allowed to access design" (3 attempts on `DAHad_yXE2Q`, 1 on `DAHad4QmPfA`). Crops are delivered instead. A person with access can export them from Canva if preferred. |
| 1 | Dedicated portrait hero art for concept 3 | Not generated (quota). Crop delivered instead. |
| 2 | Category banners: gift cards, game top-ups, subscriptions, gaming wallets | Not produced. All four generation requests failed with `quota_exceeded`. |
| 3 | Generic platform banner set (6 or more palette variations) | Not produced (quota). |
| 4 | Promotional banners (3, textless) | Not produced (quota). |
| 5 | Empty states: empty cart, no search results, no orders, empty wishlist, no tickets, 404 | Not produced (quota). |
| 6 | Marketing and campaign graphics, social templates (square and story, English and Arabic) | Not produced (quota). No English or Arabic text-bearing asset exists. |
| 7 | Default Open Graph share image | Not produced (quota). |
| 7 | Favicon and logo exports from the PLAYVAULT wordmark | Not produced. Work stopped at the quota limit. |

Also not produced from spec Phase 3: product promotional visuals, which were not in the M2 task list.

## Canva limits observed

- AI generation: `quota_exceeded` after 5 accepted design jobs and 1 image job in this session. Exports and folder moves kept working for the designs noted above.
- Resize: Canva reported 1 trial use remaining after one resize.
