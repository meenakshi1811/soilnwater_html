<?php if($post->isAstroConsultancyPost()): ?>
    <?php
        $postType = $post->astroConsultancyPostTypeLabel();
        $category = $post->astroConsultancyCategoryLabel();
        $language = data_get($post->meta, 'astro_consultancy_content_language');
        $topics = $post->astroConsultancyConsultationTopics();
        $audiences = $post->astroConsultancyTargetAudiences();
        $knowledgeTopics = $post->astroConsultancyKnowledgeLibraryTopics();
        $askCommunity = data_get($post->meta, 'astro_consultancy_ask_community');
        $consultantUrl = data_get($post->meta, 'astro_consultancy_consultant_profile_url');
        $serviceActions = (array) data_get($post->meta, 'astro_consultancy_related_service_actions', []);
        $documents = $post->astroConsultancyDocuments();
        $videoType = data_get($post->meta, 'astro_consultancy_video_type');
        $capabilities = [
            ['label' => 'Consultant directory', 'enabled' => $post->astroEnablesConsultantLinking(), 'icon' => 'fa-user-check'],
            ['label' => 'Live Q&A', 'enabled' => $post->astroEnablesLiveQa(), 'icon' => 'fa-comments'],
            ['label' => 'Knowledge library', 'enabled' => $knowledgeTopics !== [], 'icon' => 'fa-book-open'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Questions', 'enabled' => (bool) $post->allow_questions, 'icon' => 'fa-circle-question'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
        ];
    ?>

    <div class="astro-show-overview">
        <div class="astro-show-overview__kicker">Astro Consultancy · SoilnWater guidance network</div>
        <div class="astro-show-overview__title">Educational astrology, spiritual guidance, and traditional knowledge</div>
        <div class="astro-show-overview__chips">
            <?php if(filled($postType)): ?>
                <span class="astro-show-chip"><?php echo e($postType); ?></span>
            <?php endif; ?>
            <?php if(filled($category)): ?>
                <span class="astro-show-chip"><?php echo e($category); ?></span>
            <?php endif; ?>
            <?php if(filled($language)): ?>
                <span class="astro-show-chip"><?php echo e($language); ?></span>
            <?php endif; ?>
            <?php if($post->astroEnablesLiveQa()): ?>
                <span class="astro-show-chip astro-show-chip--flagship"><i class="fa-solid fa-microphone-lines me-1"></i>Live Q&amp;A</span>
            <?php endif; ?>
            <?php if($post->astroEnablesConsultantLinking()): ?>
                <span class="astro-show-chip astro-show-chip--flagship"><i class="fa-solid fa-user-check me-1"></i>Verified consultant</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if($audiences !== []): ?>
        <div class="mb-4">
            <h5 class="h6 text-muted mb-2">Target audience</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php $__currentLoopData = $audiences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audience): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?php echo e($audience); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($topics !== []): ?>
        <div class="mb-4">
            <h5 class="h6 text-muted mb-2">Consultation topics</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><?php echo e($topic); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($post->astroHasHoroscopeDetails()): ?>
        <div class="astro-flagship-banner astro-flagship-banner--horoscope d-flex align-items-start gap-3 mb-4" role="status">
            <i class="fa-solid fa-star text-warning fs-4 mt-1" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <div class="text-warning-emphasis fw-bold mb-2"><i class="fa-solid fa-moon me-1"></i>Horoscope focus</div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if(filled(data_get($post->meta, 'astro_consultancy_zodiac_sign'))): ?>
                        <span class="badge bg-warning text-dark px-3 py-2"><?php echo e(data_get($post->meta, 'astro_consultancy_zodiac_sign')); ?></span>
                    <?php endif; ?>
                    <?php if(filled(data_get($post->meta, 'astro_consultancy_horoscope_period'))): ?>
                        <span class="badge bg-light text-dark border px-3 py-2"><?php echo e(data_get($post->meta, 'astro_consultancy_horoscope_period')); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($post->astroHasVastuDetails()): ?>
        <div class="astro-flagship-banner astro-flagship-banner--vastu mb-4">
            <div class="text-info fw-bold mb-2"><i class="fa-solid fa-compass me-1"></i>Vastu guidance</div>
            <?php if($post->astroConsultancyVastuPropertyTypes() !== []): ?>
                <div class="mb-2">
                    <div class="small text-muted mb-1">Property types</div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $post->astroConsultancyVastuPropertyTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $propertyType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle"><?php echo e($propertyType); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if($post->astroConsultancyVastuAreas() !== []): ?>
                <div>
                    <div class="small text-muted mb-1">Areas covered</div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $post->astroConsultancyVastuAreas(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $area): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="badge bg-light text-dark border"><?php echo e($area); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($post->astroHasNumerologyDetails()): ?>
        <div class="astro-flagship-banner astro-flagship-banner--numerology mb-4">
            <div class="text-primary fw-bold mb-2"><i class="fa-solid fa-hashtag me-1"></i>Numerology details</div>
            <div class="row g-3">
                <?php $__currentLoopData = [
                    'Life path number' => data_get($post->meta, 'astro_consultancy_life_path_number'),
                    'Destiny number' => data_get($post->meta, 'astro_consultancy_destiny_number'),
                    'Name number' => data_get($post->meta, 'astro_consultancy_name_number'),
                    'Lucky number' => data_get($post->meta, 'astro_consultancy_lucky_number'),
                    'Compatibility' => data_get($post->meta, 'astro_consultancy_compatibility'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(filled($value)): ?>
                        <div class="col-md-4 col-lg-3">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label"><?php echo e($label); ?></span>
                                <span><?php echo e($value); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($post->astroHasGemstoneDetails()): ?>
        <div class="astro-flagship-banner astro-flagship-banner--gemstone mb-4">
            <div class="text-success fw-bold mb-2"><i class="fa-solid fa-gem me-1"></i>Gemstone guidance</div>
            <div class="row g-3">
                <?php $__currentLoopData = [
                    'Gemstone' => data_get($post->meta, 'astro_consultancy_gemstone'),
                    'Planet' => data_get($post->meta, 'astro_consultancy_gemstone_planet'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(filled($value)): ?>
                        <div class="col-md-6">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label"><?php echo e($label); ?></span>
                                <span><?php echo e($value); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(filled(data_get($post->meta, 'astro_consultancy_gemstone_benefits'))): ?>
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Traditional benefits</span>
                            <span><?php echo nl2br(e(data_get($post->meta, 'astro_consultancy_gemstone_benefits'))); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if(filled(data_get($post->meta, 'astro_consultancy_gemstone_precautions'))): ?>
                    <div class="col-md-6">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Precautions</span>
                            <span><?php echo nl2br(e(data_get($post->meta, 'astro_consultancy_gemstone_precautions'))); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($post->astroHasFestivalDetails()): ?>
        <div class="astro-flagship-banner astro-flagship-banner--festival mb-4">
            <div class="text-success fw-bold mb-2"><i class="fa-solid fa-calendar-days me-1"></i>Festival &amp; muhurat</div>
            <div class="row g-3">
                <?php $__currentLoopData = [
                    'Festival' => data_get($post->meta, 'astro_consultancy_festival_name'),
                    'Muhurat type' => data_get($post->meta, 'astro_consultancy_muhurat_type'),
                    'Time' => data_get($post->meta, 'astro_consultancy_muhurat_time'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(filled($value)): ?>
                        <div class="col-md-4">
                            <div class="business-meta-item">
                                <span class="business-meta-item__label"><?php echo e($label); ?></span>
                                <span><?php echo e($value); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(filled(data_get($post->meta, 'astro_consultancy_muhurat_date'))): ?>
                    <div class="col-md-4">
                        <div class="business-meta-item">
                            <span class="business-meta-item__label">Date</span>
                            <span><?php echo e(\Illuminate\Support\Carbon::parse(data_get($post->meta, 'astro_consultancy_muhurat_date'))->format('d M Y')); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if(filled(data_get($post->meta, 'astro_consultancy_festival_significance'))): ?>
                <div class="mt-3">
                    <div class="business-meta-item__label mb-1">Traditional significance</div>
                    <p class="small mb-0"><?php echo nl2br(e(data_get($post->meta, 'astro_consultancy_festival_significance'))); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($knowledgeTopics !== []): ?>
        <div class="business-section-panel about-box mb-4 border-info">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-book-open text-info" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Astrology knowledge library</h4>
                    <p class="text-muted small mb-0">Educational topics covered in this post.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php $__currentLoopData = $knowledgeTopics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2"><?php echo e($topic); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if($post->astroEnablesLiveQa()): ?>
        <div class="business-section-panel about-box mb-4 border-warning">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-microphone-lines text-warning" aria-hidden="true"></i>
                <div>
                    <h4 class="mb-0">Live Q&amp;A sessions</h4>
                    <p class="text-muted small mb-0">This post is marked for live Q&amp;A or archived session discovery. Use private consultation below instead of sharing personal details publicly.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="business-section-panel about-box mb-4">
        <div class="business-section-panel__header">
            <i class="fa-solid fa-sliders text-secondary" aria-hidden="true"></i>
            <h4 class="mb-0">Post capabilities</h4>
        </div>
        <div class="astro-capability-grid">
            <?php $__currentLoopData = $capabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $capability): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="astro-capability-pill <?php echo e($capability['enabled'] ? '' : 'is-disabled'); ?>">
                    <i class="fa-solid <?php echo e($capability['icon']); ?>" aria-hidden="true"></i>
                    <?php echo e($capability['label']); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <?php if($post->featuredImageUrl() || $post->hasVideo() || $documents !== []): ?>
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-images text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Media &amp; documents</h4>
            </div>

            <?php if($post->featuredImageUrl()): ?>
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">Featured image</div>
                    <img src="<?php echo e($post->featuredImageUrl()); ?>" alt="Featured astro consultancy image" class="img-fluid rounded border">
                </div>
            <?php endif; ?>

            <?php if($post->hasVideo()): ?>
                <div class="mb-3">
                    <div class="business-meta-item__label mb-2">
                        Video@if(filled($videoType)) · <?php echo e($videoType); ?><?php endif; ?>
                    </div>
                    <?php if($post->youtubeEmbedUrl()): ?>
                        <div class="ratio ratio-16x9 rounded overflow-hidden"><iframe src="<?php echo e($post->youtubeEmbedUrl()); ?>" title="Astro consultancy video" allowfullscreen></iframe></div>
                    <?php elseif($post->videoFileUrl()): ?>
                        <video controls class="w-100 rounded" preload="metadata"><source src="<?php echo e($post->videoFileUrl()); ?>"></video>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($documents !== []): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(data_get($document, 'url')); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fa-solid fa-file-lines me-1"></i><?php echo e(data_get($document, 'name', 'Document')); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(filled($consultantUrl) || $serviceActions !== []): ?>
        <div class="business-section-panel about-box mb-4 border-primary">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-user-check text-primary" aria-hidden="true"></i>
                <h4 class="mb-0">Related services</h4>
            </div>
            <?php if(filled($consultantUrl)): ?>
                <p class="mb-2">
                    <a href="<?php echo e($consultantUrl); ?>" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View consultant profile
                    </a>
                </p>
            <?php endif; ?>
            <?php if($serviceActions !== []): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $serviceActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2"><?php echo e($action); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php echo $__env->make('community.partials.astro-consultancy-meta-details', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="astro-disclaimer-panel p-3 p-lg-4 mt-4 mb-0 small" role="note">
        <div class="fw-semibold mb-1 text-warning-emphasis"><i class="fa-solid fa-triangle-exclamation me-1"></i>Disclaimer</div>
        <p class="mb-0"><?php echo e(\App\Support\CommunityContentTaxonomy::astroConsultancyDisclaimerText()); ?></p>
    </div>
<?php endif; ?>
<?php /**PATH E:\xampp\htdocs\soilNwater\resources\views/community/partials/astro-consultancy-show-sections.blade.php ENDPATH**/ ?>