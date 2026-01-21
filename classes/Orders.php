<?php
require_once("config/env.php");

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
		// DATA MAPPING (Support both Spanish and English keys)
		$carrito      = (isset($item['cart'])) ? $item['cart'] : $item['carrito'];
		$productos    = $carrito["items"];
		$fechaEntrega = (isset($item['deliveryDate'])) ? $item['deliveryDate'] : $item['fechaEntrega'];
		
		// Date Construction
		$anio  = (isset($fechaEntrega["year"])) ? $fechaEntrega["year"] : $fechaEntrega["anioElegido"];
		$mes   = (isset($fechaEntrega["month"])) ? $fechaEntrega["month"] : $fechaEntrega["mesElegido"];
		$dia   = (isset($fechaEntrega["day"])) ? $fechaEntrega["day"] : $fechaEntrega["diaElegido"];
		$fechaDeEntrega = "$anio-$mes-$dia";
		
		$horario       = (isset($fechaEntrega["schedule"])) ? $fechaEntrega["schedule"] : $fechaEntrega["horario"];
		$modoPago      = (isset($item["paymentMethod"])) ? $item["paymentMethod"] : $item["modoPago"];
		$observaciones = (isset($item["observations"])) ? $item["observations"] : $item["observaciones"];
		$usuario       = (isset($item["user"])) ? $item["user"] : $item["usuario"];
		$total         = $item["total"];
		
		// User specific fields
		$idUsuario      = (isset($usuario["id"])) ? $usuario["id"] : $usuario["idUsuario"];
		$tel            = (isset($usuario["phone"])) ? $usuario["phone"] : $usuario["tel"];
		$idBarrio       = (isset($usuario["neighborhoodId"])) ? $usuario["neighborhoodId"] : $usuario["idBarrio"];
		
		// Address Construction
		$calle   = (isset($usuario["street"])) ? $usuario["street"] : $usuario["calle"];
		$numero  = (isset($usuario["number"])) ? $usuario["number"] : $usuario["numero"];
		$apto    = (isset($usuario["apartment"])) ? $usuario["apartment"] : $usuario["apto"];
		$esquina = (isset($usuario["corner"])) ? $usuario["corner"] : $usuario["esquina"];
		$direccionEntrega = "$calle $numero / $apto esquina: $esquina";
		
		$estado = 0; // New order status
		$cantProds = count($productos);

		$sql_pedido = "INSERT INTO pedidos (fechaPedido, idUsuario, estado, fechaEntrega, horarioEntrega, telContacto, dirEntrega, observaciones, modoPago, total, idBarrio) VALUES (";
		$sql_pedido .= "NOW(), ";
		$sql_pedido .= "'$idUsuario', ";
		$sql_pedido .= "'$estado', ";
		$sql_pedido .= "'$fechaDeEntrega', ";
		$sql_pedido .= "'$horario', ";
		$sql_pedido .= "'$tel', ";
		$sql_pedido .= "'$direccionEntrega', ";
		$sql_pedido .= "'$observaciones', ";
		$sql_pedido .= "'$modoPago', ";
		$sql_pedido .= "'$total', ";
		$sql_pedido .= "'$idBarrio')";

		if(!empty($item)){
			$datos 	= $conn->query($sql_pedido);
			if(empty($datos)){
				// OBTENGO LA ID DEL PEDIDO
				$idPedido = mysql_insert_id();
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
					if(!empty($registros)){
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

				//ENVIAR MAIL AL CLIENTE
				$email_to = $usuario["email"] ? $usuario["email"] : $usuario["mail"];
				$email_subject = "Compra web";
				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>¡Gracias por tu compra!</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>Tu pedido ha sido confirmado con éxito. 🎉</p><p style='font-size: 16px;'>Lo enviaremos en la fecha y horario seleccionado.</p>";
					$email_message = $header_body_email;
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Número de pedido:</strong> #".$idPedido."</p></div>";
						$email_message .= "<a href='".SITE_URL."/pedidos/".$idPedido."/".$idUsuario."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Ver mi pedido</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Si tienes alguna pregunta, contáctanos en <a href='mailto:alaskacongelados@gmail.com' style='color: #6a11cb; text-decoration: none;'>alaskacongelados@gmail.com</a></p>";
					$email_message .= "</div>"; //TO CLOSE
				$email_message .= "<div style='background: #eef2ff; text-align: center; padding: 15px; font-size: 14px; color: #666;'>© 2025 Alaska Congelados | Todos los derechos reservados</div></div>";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$headers .= 'From: Alaska Congelados<alaskacongelados@gmail.com>' . "\r\n";

				@mail($email_to, $email_subject, $email_message, $headers);

				//ENVIAR MAIL A LA EMPRESA
				$email_to = 'pedidosalaskacongelados@gmail.com';
				$email_subject = "Compra web";
				$header_body_email = "<div style='max-width: 600px; margin: auto; font-family: Arial, sans-serif; background: #f9f9f9; border-radius: 10px; overflow: hidden;'><div style='background: linear-gradient(135deg, #6a11cb, #2575fc); color: #fff; text-align: center; padding: 25px;'><h1 style='margin: 0; font-size: 24px;'>¡Se ha realizado una compra web!</h1></div><div style='padding: 20px; color: #333; text-align: center;'><p style='font-size: 18px;'>El pedido ha sido confirmado con éxito. 🎉</p>";
					$email_message = $header_body_email;
					$email_message .= "<div style='background: #eef2ff; padding: 15px; border-radius: 8px; margin: 20px auto; display: inline-block;'><p style='margin: 0; font-size: 16px;'><strong>Número de pedido:</strong> #".$idPedido."</p></div>";
						$email_message .= "<a href='".SITE_URL."/pedidos/".$idPedido."/".$idUsuario."' target='_blank' style='background: #6a11cb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-top: 15px;'>Ver el pedido</a>";
						$email_message .="<p style='margin-top: 20px; font-size: 14px; color: #666;'>Si el cliente tiene dudas se comunicará a <a href='mailto:alaskacongelados@gmail.com' style='color: #6a11cb; text-decoration: none;'>alaskacongelados@gmail.com</a></p>";
					$email_message .= "</div>"; //TO CLOSE
				$email_message .= "<div style='background: #eef2ff; text-align: center; padding: 15px; font-size: 14px; color: #666;'>© 2025 Alaska Congelados | Todos los derechos reservados</div></div>";
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				// More headers
				$headers .= 'From: Alaska Congelados<alaskacongelados@gmail.com>' . "\r\n";

				@mail($email_to, $email_subject, $email_message, $headers);

				$d = array("response" => "Ok");
				return $d;

			} else {
				$d = array("response" => "err: to add order");
				return $d;
			}
		}else{
			$d = array("response" => "err: item is empty");
			return $d;
		}
	}
	// GET all orders (Admin)
	public function getAll($conn, $params = array()){
		$limit 	= (isset($params["limit"])) ? (int)$params["limit"] : 100;
		$offset = (isset($params["offset"])) ? (int)$params["offset"] : 0;
		$whereBase = "WHERE 1=1";

		if(isset($params["where"]["seen"]) && $params["where"]["seen"] !== ""){
			$seenVal = (int)$params["where"]["seen"];
			if($seenVal === 1){
				// Seen is strictly string 'true'
				$whereBase .= " AND p.visto = 'true'";
			} else {
				// Unseen is anything NOT 'true' (NULL, 'false', empty, 0, etc.)
				$whereBase .= " AND (p.visto != 'true' OR p.visto IS NULL)";
			}
		}

		// Count Items
		$sqlCount = "SELECT COUNT(*) as total FROM pedidos p ".$whereBase;
		$countData = $conn->query($sqlCount);
		
		// If query error
		if (is_string($countData)) return array("error" => "SQL Error in Count", "message" => $countData);
		
		$total = (isset($countData[0]["total"])) ? $countData[0]["total"] : 0;

		$sql = "SELECT 
					p.idPedido AS orderId, 
					p.fechaPedido AS orderDate, 
					p.horaPedido AS orderHour, 
					p.total AS total, 
					IF(p.visto = 'true', 1, 0) AS seen, 
					p.estado AS status,
					u.nomUsuario AS name,
					u.apellido AS lastName,
					u.mail AS email,
					p.fechaEntrega AS deliveryDate,
					p.horarioEntrega AS deliveryHour,
					p.telContacto AS phone,
					p.dirEntrega AS address,
					p.observaciones AS observations,
					p.modoPago AS paymentMethod,
					p.idBarrio AS neighborhoodId,
					p.idUsuario AS userId
				FROM pedidos p
				LEFT JOIN usuarios u ON p.idUsuario = u.idUsuario
				".$whereBase." 
				ORDER BY p.idPedido DESC 
				LIMIT $offset, $limit";
		
		$d = $conn->query($sql);
		
		// If query error
		if (is_string($d)) return array("error" => "SQL Error in Data", "message" => $d);
		
		if ($d == "") {
			$d = array();
		}
		
		return array("data" => $d, "count" => (int)$total);
	}

	// Update Order (Admin)
	public function update($conn, $orderId, $data){
		$updates = array();

		if(isset($data['seen'])) $updates[] = "visto = '".$data['seen']."'";
		if(isset($data['status'])) $updates[] = "estado = '".$data['status']."'";

		if (empty($updates)) {
			return array("response" => "OK", "message" => "No fields to update", "success" => true);
		}

		$sql = "UPDATE pedidos SET " . implode(', ', $updates) . " WHERE idPedido = '$orderId'";
		
		$d = $conn->query($sql);
		
		if(empty($d)){
			return array("response" => "OK", "success" => true);
		} else {
			return array("error" => "Error al actualizar el pedido.", "sql" => $sql);
		}
	}

	// GET order detail by id (Admin)
	public function getById($conn, $orderId){
		// First get order basic info
		$sql = "SELECT 
					p.idPedido AS orderId,
					p.fechaPedido AS orderDate,
					p.horaPedido AS orderHour,
					p.fechaEntrega AS deliveryDate,
					p.horarioEntrega AS deliverySchedule,
					p.telContacto AS contactPhone,
					p.dirEntrega AS deliveryAddress,
					p.observaciones AS observations,
					p.modoPago AS paymentMethod,
					p.total AS total,
					p.visto AS seen,
					p.estado AS status,
					u.nomUsuario AS name,
					u.apellido AS lastName,
					u.mail AS email,
					u.tel AS userPhone,
					b.title AS neighborhoodName
				FROM pedidos p
				LEFT JOIN usuarios u ON p.idUsuario = u.idUsuario
				LEFT JOIN barrios b ON p.idBarrio = b.idBarrio
				WHERE p.idPedido = '$orderId'";
		
		$orderData = $conn->query($sql);
		
		if(!empty($orderData)){
			$order = $orderData[0];
			
			// Get order details (products)
			$sqlProducts = "SELECT 
								dp.idProducto AS productId,
								pr.nombre AS name,
								dp.cantidad AS quantity,
								dp.subtotal AS amount,
								pr.foto AS image
							FROM detallesPedido dp
							JOIN productos pr ON dp.idProducto = pr.idProducto
							WHERE dp.idPedido = '$orderId'";
			
			$products = $conn->query($sqlProducts);

			if(!empty($products)){
				foreach($products as $key => $prod){
					if(isset($prod['image']) && $prod['image'] != ""){
						$products[$key]['image'] = UPLOAD_URL . $prod['image'];
					}
				}
			}

			$order['products'] = (!empty($products)) ? $products : array();
			
			return array("response" => 'OK', "data" => $order);
		} else {
			return array("error" => "Error: pedido no encontrado.");
		}
	}
	
}

?>
