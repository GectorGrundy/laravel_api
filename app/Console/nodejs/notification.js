'use strict';

require('dotenv').config({path: __dirname + '/../../../.env'});
var server = require('http').createServer(handler);
var io = require('socket.io')(server);
var jwt = require('jsonwebtoken');
var Redis = require('ioredis');
var redis = new Redis({
	port: process.env.REDIS_BROADCAST_PORT,
	host: process.env.REDIS_BROADCAST_HOST,
	family: 4,
	db: 0
});

server.listen(process.env.SOCKET_PORT, process.env.SOCKET_IP);

// Subscribe for receiving broadcasted events
redis.subscribe('user-balance-changed', 'user-logout');
redis.on('message', function (chanel, message) {
	message = JSON.parse(message);

	Notification.generate(message.data.user_hash, {action: chanel, data: message.data});
});

var clients = {};

io.on('connection', function (socket) {

	socket.on('auth', function (data) {
		jwt.verify(data.token, process.env.JWT_SECRET, {algorithms: ['HS256']}, function (err, decoded) {
			// console.log('Connected user: ' + decoded.user_hash + '; Soket: ' + socket.id);

			if (err) {
				console.log('unauthorized');
				return false;
			}

			// Добавляем клиента, если его там еще нету
			if (!clients.hasOwnProperty(decoded.user_hash)) {
				clients[decoded.user_hash] = {sockets: [], 'user': {token: data.token, user_hash: decoded.user_hash}};
				clients[decoded.user_hash].sockets.push(socket.id);

			} else {
				// Добавляем инфо о сокетах клиента
				clients[decoded.user_hash].sockets.push(socket.id);
			}
		});
	});

	socket.on('updateCountUnreadedTickets', function (data) {
		Notification.generate(data.user_hash, {action: data.action});
	});

	socket.on('insertMessageInTicket', function (data) {
		Notification.generate(data.user_hash, {action: data.action, data: data});
	});

	socket.on('disconnect', function () {
		// Удаление сокета с массива
		for (var user_hash in clients) {
			// Перебор сокетов пользователя
			for (var socket_index in clients[user_hash].sockets) {
				if (clients[user_hash].sockets[socket_index] == socket.id) {
					// Удаление из массива закрытого сокета
					clients[user_hash].sockets.splice(socket_index, 1);
					return false;
				}
			}
		}
	});
});

var Notification = {
	generate: function (user_hash, params) {
		user_hash = user_hash || 0;

		if (user_hash < 1) {
			console.log('no receiver');
			return;
		}

		if (typeof clients[user_hash] !== 'object') {
			console.log('unsent message to ' + user_hash);
			return false;
		}

		var sent = false;
		var userSockets = clients[user_hash].sockets;

		for (var i = 0; i < userSockets.length; i++) {
			try {
				io.to(userSockets[i]).emit(params.action, JSON.stringify(params));

				sent = true;
			} catch (e) {
				// delete clients[user_hash].io[sockets_ordered[k]];
				sent = false;
				console.log('send false #1');
			}
		}
		return true;
	}

};

function handler(req, res) {
	res.writeHead(200);
	res.end('');
}
