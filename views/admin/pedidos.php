<?php global $viewData; $d = $viewData ?? []; $filtro = $d['filtro'] ?? 'pendiente'; ?>
<div class="card">
    <div class="card-header-custom"><h2>Pedidos</h2></div>
    <div class="tabs">
        <a href="index.php?page=admin&module=pedidos&filtro=pendiente" class="tab <?=$filtro==='pendiente'?'active':''?>">Pendientes</a>
        <a href="index.php?page=admin&module=pedidos&filtro=entregado" class="tab <?=$filtro==='entregado'?'active':''?>">Entregados</a>
        <a href="index.php?page=admin&module=pedidos&filtro=todos" class="tab <?=$filtro==='todos'?'active':''?>">Todos</a>
    </div>
    <?php if(empty($d['pedidos'])):?>
        <p class="text-muted text-center" style="padding:40px;">No hay pedidos <?=$filtro!=='todos'?$filtro.'s':''?></p>
    <?php else:?>
    <div style="padding:12px 0;">
        <input type="text" id="filtroPedido" class="form-control" placeholder="Buscar por codigo de pedido..." style="max-width:350px;">
    </div>
    <div class="order-cards" id="orderCards">
        <?php foreach($d['pedidos'] as $p):?>
        <div class="order-card">
            <div class="oc-header">
                <strong><?=htmlspecialchars($p['codigo_pedido'])?></strong>
                <span class="badge <?=$p['status']==='pendiente'?'badge-warning':($p['status']==='entregado'?'badge-success':'badge-danger')?>"><?=$p['status']?></span>
            </div>
            <div class="oc-items">
                <strong>Cliente:</strong> <?=htmlspecialchars($p['cliente_nombre']??'Venta directa')?><br>
                <strong>Tipo:</strong> <?=htmlspecialchars($p['tipo']??'')?><br>
                <strong>Fecha:</strong> <?=$p['fecha']?> <?=$p['hora']?><br>
                <strong>Productos:</strong>
                <div style="margin-top:8px;">
                    <?php foreach(($p['items']??[]) as $item):?>
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid #eee;">
                        <?php if(!empty($item['producto_imagen'])):?>
                            <img src="<?=htmlspecialchars($item['producto_imagen'])?>" style="width:45px;height:45px;object-fit:cover;border-radius:4px;">
                        <?php else:?>
                            <div style="width:45px;height:45px;background:#eef1f8;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:0.7rem;">Sin img</div>
                        <?php endif;?>
                        <div style="flex:1;">
                            <strong><?=htmlspecialchars($item['producto_nombre']??'Producto')?></strong><br>
                            <small>x <?=$item['cantidad']?> - Bs <?=number_format($item['subtotal'],2)?></small>
                        </div>
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
            <div class="oc-total">Total: Bs <?=number_format(array_sum(array_column($p['items']??[],'subtotal')),2)?></div>
            <?php if($p['status']==='pendiente'):?>
            <div class="oc-footer">
                <form method="POST"><?=csrf_field()?><input type="hidden" name="form_action" value="entregar"><input type="hidden" name="pedido_id" value="<?=$p['id']?>">
                    <select name="metodo_pago" style="padding:6px;border-radius:4px;border:1px solid #ccc;"><option value="1">Efectivo</option><option value="2">QR</option></select>
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Marcar como entregado?')">Entregar</button>
                </form>
            </div>
            <?php endif;?>
        </div>
        <?php endforeach;?>
    </div>
    <?php endif;?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    var input = document.getElementById('filtroPedido');
    if (!input) return;
    var cards = document.querySelectorAll('#orderCards .order-card');
    input.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        cards.forEach(function(card){
            var codigo = card.querySelector('.oc-header strong');
            var text = codigo ? codigo.textContent.toLowerCase() : '';
            card.style.display = (!q || text.indexOf(q) !== -1) ? '' : 'none';
        });
    });
});
</script>
