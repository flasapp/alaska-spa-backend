<?php
require("config/env.php");

class Order {
	private string $model = "users";

	/**
	 * Get orders by user ID
	 * 
	 * @param Connection $conn Database connection
	 * @param int $userId User ID
	 * @return array Response with orders data or error
	 */
	public function getOrdersByUser(Connection $conn, int $userId): array {
		try {
			$sql = "SELECT estado AS status, fechaEntrega AS deliveryDate, fechaPedido AS orderDate, 
								horaPedido AS orderHour, horarioEntrega AS deliverySchedule, idBarrio AS neighbourhoodId, 
								idPedido AS orderId, idUsuario AS userId, modoPago AS paymentMethod, 
								observaciones AS observations, ordenCompra AS purchaseOrder, 
								telContacto AS contactPhone, total AS total, visto AS seen 
						FROM pedidos 
						WHERE idUsuario = ? 
						ORDER BY idPedido DESC";
			
			$stmt = $conn->prepare($sql);
			$stmt->execute([$userId]);
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			return [
				"response" => 'OK',
				"data" => $data
			];
		} catch (PDOException $e) {
			return [
				"error" => "Error: al obtener los pedidos.",
				"sql" => $sql,
				"exception" => $e->getMessage()
			];
		}
	}

	//GET order by user
	public function getOrderByUser($conn, $orderId, $userId){
		$sql = "SELECT 
						p.idPedido AS orderId,
						p.fechaEntrega AS deliveryDate,
						p.fechaPedido AS orderDate,
						p.horaPedido AS orderHour,
						p.horarioEntrega AS deliverySchedule,
						p.observaciones AS observations,
						p.telContacto AS contactPhone,
						p.total AS total,
						p.visto AS seen,
						p.dirEntrega AS deliveryAddress,

						CONCAT('[', GROUP_CONCAT(
							CONCAT(
								'{\"productId\":', pr.idProducto,
								',\"name\":\"', pr.nombre, '\"',
								',\"description\":\"', pr.descripcion, '\"',
								',\"img\":\"', pr.foto, '\"',
								',\"quantity\":\"', dp.cantidad, '\"',
								',\"amount\":\"', dp.subtotal, '\"',
								'}'
							)
						), ']') AS products

					FROM 
						detallesPedido dp
					JOIN 
						productos pr ON dp.idProducto = pr.idProducto
					JOIN 
						pedidos p ON dp.idPedido = p.idPedido
					WHERE 
						p.idPedido = ${orderId} AND p.idUsuario = ${userId}";

		$d 		= $conn->query($sql);
		// CALLBACK
		if(!empty($d)){
			return array("response" => 'OK', "data" => $d);
		} else {
			return array("error" => "Error: al obtener los pedidos.", "sql" => $sql);
		}
	}

	//New order
	public function newOrder($conn, $item){
		// $d = array("response" => $item);
		$carrito = $item['carrito'];
		$productos = $carrito["items"];
		$fechaEntrega = $item["fechaEntrega"];
		//$fechaDeEntrega = $fechaEntrega["diaElegido"].'-'.$fechaEntrega["mesElegido"].'-'.$fechaEntrega["anioElegido"];
		$fechaDeEntrega = $fechaEntrega["anioElegido"].'-'.$fechaEntrega["mesElegido"].'-'.$fechaEntrega["diaElegido"];
		$modoPago = $item["modoPago"];
		$observaciones = $item["observaciones"];
		$usuario = $item["usuario"];
		$total = $item["total"];
		$direccionEntrega = $usuario["calle"]." ".$usuario["numero"]." / ".$usuario["apto"]." esquina : ".$usuario["esquina"];
		$estado = 0;

		$cantProds 		= count($productos);

		$sql_pedido = "INSERT INTO pedidos (fechaPedido, idUsuario, estado, fechaEntrega, horarioEntrega, telContacto, dirEntrega, observaciones, modoPago, total, idBarrio) VALUES (";
		$sql_pedido .= 'NOW(),';
		$sql_pedido .= $usuario["idUsuario"].',';
		$sql_pedido .= $estado.',"';
		$sql_pedido .= $fechaDeEntrega.'","';
		$sql_pedido .= $fechaEntrega["horario"].'",';
		$sql_pedido .= $usuario["tel"].',"';
		$sql_pedido .= $direccionEntrega.'","';
		$sql_pedido .= $observaciones.'","';
		$sql_pedido .= $modoPago.'","';
		$sql_pedido .= $total.'",';
		$sql_pedido .= $usuario["idBarrio"].')';
		/*
		$datos 	= $conn->query($sql_pedido);
		if($datos != 0){
			die("Err: en insertar el pedido en la base de datos");
		}
		*/
		if(!empty($item)){
			$datos 	= $conn->query($sql_pedido);
			if($datos == ""){
				// OBTENGO LA ID DEL PEDIDO
				$idPedido = mysqli_insert_id($conn);
				/* CREO EL DETALLE DEL PEDIDO A INSERTAR */
				$sql_detallesPedido = "INSERT INTO detallesPedido (idPedido,idProducto,cantidad,subtotal) VALUES ";

				for ($i = 0; $i < $cantProds; $i++) {
					$id_producto 	= $productos[$i]['_id'];
					$nombre 	= $productos[$i]['_name'];
					$precio 	= (float) $productos[$i]['_price'];
					$cantidad 	= (int) $productos[$i]['_quantity'];
					$subtotal 	= $precio * $cantidad;

					$sql_detallesPedido .= "(".$idPedido.",".$id_producto.",".$cantidad.",".$subtotal.")";


					if($i < $cantProds - 1){
						// si es el último no agregamos espacio-coma
						$sql_detallesPedido .= " , ";
					}

					$query 	= "SELECT * FROM masVendidos WHERE idProducto = '$id_producto'";
					$registros 	= $conn->query($query);
					if($registros != 0){
						$registros = $registros[0];
						$cant 	= $registros['cant'] + $cantidad;
						$query2 = "UPDATE masVendidos SET cant = '$cant' WHERE idProducto = ".$id_producto;
					}else{
						$cant 	= $cantidad;
						$query2 = "INSERT INTO masVendidos(idProducto, cant) VALUES('$id_producto','$cant')";
					}
					$registros2 	= $conn->query($query2);

				}
				/* INSERTO EL DETALLE DEL PEDIDO EN LA BD */
				$resultado_detallesPedido = $conn->query($sql_detallesPedido);

				if($resultado_detallesPedido != 0){
					die("Err: en insertar el detalle de pedido en la base de datos");
				}

				//ENVIAR MAIL AL CLIENTE
				$email_to = $usuario["mail"];
				$email_subject = "Compra web";
				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>¡Gracias por tu compra!</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>Tu pedido ha sido confirmado con éxito. 🎉</p><p style='font-size: 16px;'>Lo enviaremos en la fecha y horario seleccionado.</p>";
					$email_message = $header_body_email;
					// $email_message .= "<br>";
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Número de pedido:</strong> #".$idPedido."</p></div>";
						$email_message .= "<a href='".SITE_URL."/pedidos/".$idPedido."/".$usuario["idUsuario"]."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Ver mi pedido</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Si tienes alguna pregunta, contáctanos en <a href='mailto:alaskacongelados@gmail.com' style='color: #6a11cb; text-decoration: none;'>alaskacongelados@gmail.com</a></p>";
					$email_message .= "</div>"; //TO CLOSE
				$email_message .= "<div style='background: #eef2ff; text-align: center; padding: 15px; font-size: 14px; color: #666;'>© 2025 Alaska Congelados | Todos los derechos reservados</div></div>";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$headers .= 'From: Alaska Congelados<alaskacongelados@gmail.com>' . "\r\n";
				// $headers .= 'Cco: pedidosalaskacongelados@gmail.com' . "\r\n";

				error_reporting(0);
			mail($email_to, $email_subject, $email_message, $headers);
			error_reporting(E_ALL);

				//ENVIAR MAIL A LA EMPRESA
				$email_to = 'pedidosalaskacongelados@gmail.com';

				$email_subject = "Compra web";

				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>¡Se ha realizado una compra web!</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>El pedido ha sido confirmado con éxito. 🎉</p>";
					$email_message = $header_body_email;
					// $email_message .= "<br>";
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Número de pedido:</strong> #".$idPedido."</p></div>";
						$email_message .= "<a href='".$site_url."/pedidos/".$idPedido."/".$usuario["idUsuario"]."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Ver el pedido</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Si el cliente tiene dudas se comunicará a <a href='mailto:alaskacongelados@gmail.com' style='color: #6a11cb; text-decoration: none;'>alaskacongelados@gmail.com</a></p>";
					$email_message .= "</div>"; //TO CLOSE
				$email_message .= "<div style='background: #eef2ff; text-align: center; padding: 15px; font-size: 14px; color: #666;'>© 2025 Alaska Congelados | Todos los derechos reservados</div></div>";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$headers .= 'From: Alaska Congelados<alaskacongelados@gmail.com>' . "\r\n";
				// $headers .= 'Cco: pedidosalaskacongelados@gmail.com' . "\r\n";

				error_reporting(0);
			mail($email_to, $email_subject, $email_message, $headers);
			error_reporting(E_ALL);


				$d = array("response" => "Ok");
				return $d;

			} else {
				$d = array("response" => "err: to add order");
				return $d;
			}
		}else{
			$d = "else del empty";
			return $d;
		}
	}
	
}

?>
