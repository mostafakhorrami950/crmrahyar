<div class="card p-0">
    <h5 class="fw-bold mb-0">تحلیل پایپ لاین</h5>
    <?php foreach ($pipelineData as $pipelineName => $stages): ?>
    <h6 style="color:var(--primary);font-weight:bold;margin:20px 0 10px;"><?php echo htmlspecialchars($pipelineName); ?></h6>
    <div class="table-responsive mb-4"><table class="table table-sm"><thead><tr><th class="text-nowrap">مرحله</th><th class="text-nowrap">تعداد معاملات</th><th class="text-nowrap">مجموع مبلغ</th></tr></thead>
        <tbody><?php foreach ($stages as $s): ?><tr><td><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?php echo $s->color; ?>;margin-left:5px;"></span><?php echo htmlspecialchars($s->stage_name); ?></td><td><?php echo $s->deals_count; ?></td><td><strong><?php echo number_format($s->total_amount); ?></strong></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endforeach; ?>
</div>