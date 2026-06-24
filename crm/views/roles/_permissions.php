<?php
/**
 * Reusable permission selector partial
 * Variables: $modules (from RoleController::getPermissionModules())
 *            $rolePermsMap (optional, for edit mode - array of slug => scope)
 */
$rolePermsMap = $rolePermsMap ?? [];
?>
<div class="perm-modules">
    <?php foreach ($modules as $moduleName => $perms): ?>
    <div class="perm-module">
        <div class="perm-module-header" onclick="toggleModule(this)">
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="perm-module-arrow">▶</span>
                <strong><?php echo $perms[0]['icon']; ?> <?php echo htmlspecialchars($moduleName); ?></strong>
            </div>
            <label class="perm-select-all" onclick="event.stopPropagation()">
                <input type="checkbox" onchange="toggleAllInModule(this)" style="cursor:pointer;">
                <span style="font-size:11px;color:var(--gray-500);">انتخاب همه</span>
            </label>
        </div>
        <div class="perm-module-body" style="display:none;">
            <?php foreach ($perms as $perm): 
                $isChecked = isset($rolePermsMap[$perm['slug']]);
                $currentScope = $isChecked ? $rolePermsMap[$perm['slug']] : 'all';
            ?>
            <div class="perm-row" data-slug="<?php echo $perm['slug']; ?>">
                <label class="perm-label">
                    <input type="checkbox" name="permissions[]" value="<?php echo $perm['slug']; ?>" 
                           <?php echo $isChecked ? 'checked' : ''; ?>
                           onchange="toggleScopeRow(this)" style="cursor:pointer;">
                    <span><?php echo htmlspecialchars($perm['name']); ?></span>
                </label>
                <?php if ($perm['hasScope']): ?>
                <div class="perm-scope" style="<?php echo $isChecked ? '' : 'opacity:0.3;pointer-events:none;'; ?>">
                    <label class="scope-radio">
                        <input type="radio" name="scopes[<?php echo $perm['slug']; ?>]" value="all" 
                               <?php echo $currentScope === 'all' ? 'checked' : ''; ?>>
                        <span class="scope-badge scope-all">🌐 همه</span>
                    </label>
                    <label class="scope-radio">
                        <input type="radio" name="scopes[<?php echo $perm['slug']; ?>]" value="own" 
                               <?php echo $currentScope === 'own' ? 'checked' : ''; ?>>
                        <span class="scope-badge scope-own"><i class="bi bi-person me-1"></i> فقط خودش</span>
                    </label>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.perm-modules { display:flex;flex-direction:column;gap:8px; }
.perm-module { border:1px solid var(--gray-200);border-radius:10px;overflow:hidden; }
.perm-module-header { display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:var(--gray-50);cursor:pointer;user-select:none; }
.perm-module-header:hover { background:var(--gray-100); }
.perm-module-arrow { font-size:10px;color:var(--gray-400);transition:transform 0.2s;display:inline-block; }
.perm-module-header.open .perm-module-arrow { transform:rotate(90deg); }
.perm-module-body { padding:8px; }
.perm-row { display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:8px; }
.perm-row:hover { background:var(--gray-50); }
.perm-label { display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;flex:1; }
.perm-scope { display:flex;gap:4px;flex-shrink:0; }
.scope-radio { display:flex;align-items:center;cursor:pointer; }
.scope-radio input { display:none; }
.scope-badge { padding:3px 10px;border-radius:16px;font-size:11px;font-weight:600;border:2px solid transparent;transition:all 0.15s; }
.scope-badge.scope-all { background:var(--gray-100);color:var(--gray-500); }
.scope-badge.scope-own { background:var(--gray-100);color:var(--gray-500); }
.scope-radio input:checked + .scope-badge.scope-all { background:#dbeafe;color:#1e40af;border-color:#3B82F6; }
.scope-radio input:checked + .scope-badge.scope-own { background:#fef3c7;color:#92400e;border-color:#F59E0B; }
</style>

<script>
function toggleModule(header) {
    header.classList.toggle('open');
    var body = header.nextElementSibling;
    body.style.display = body.style.display === 'none' ? 'block' : 'none';
}

function toggleAllInModule(checkbox) {
    var module = checkbox.closest('.perm-module');
    var permCheckboxes = module.querySelectorAll('input[name="permissions[]"]');
    permCheckboxes.forEach(function(cb) {
        cb.checked = checkbox.checked;
        toggleScopeRow(cb);
    });
}

function toggleScopeRow(checkbox) {
    var row = checkbox.closest('.perm-row');
    var scopeDiv = row.querySelector('.perm-scope');
    if (scopeDiv) {
        scopeDiv.style.opacity = checkbox.checked ? '1' : '0.3';
        scopeDiv.style.pointerEvents = checkbox.checked ? '' : 'none';
        if (!checkbox.checked) {
            // Reset to 'all' when unchecked
            var allRadio = scopeDiv.querySelector('input[value="all"]');
            if (allRadio) allRadio.checked = true;
        }
    }
}

// Auto-expand modules that have checked items on load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.perm-module').forEach(function(mod) {
        var checked = mod.querySelectorAll('input[name="permissions[]"]:checked');
        if (checked.length > 0) {
            var header = mod.querySelector('.perm-module-header');
            header.classList.add('open');
            header.nextElementSibling.style.display = 'block';
        }
    });
});
</script>