<?php

class Order {
	private $model = "users";

	//GET orders by user
	public function getOrdersByUser($conn, $userId){
		$sql	= "SELECT estado AS status, fechaEntrega AS deliveryDate, fechaPedido AS orderDate, horaPedido AS orderHour, horarioEntrega AS deliverySchedule, idBarrio AS neighbourhoodId, idPedido AS orderId, idUsuario AS userId, modoPago AS paymentMethod, observaciones AS observations, ordenCompra AS purchaseOrder, telContacto AS contactPhone, total AS total, visto AS seen FROM pedidos WHERE idUsuario = '$userId' ORDER BY idPedido DESC;";
		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			return array("response" => 'OK', "data" => $d);
		} else {
			return array("error" => "Error: al obtener los pedidos.", "sql" => $sql);
		}
	}
	
}

?>
