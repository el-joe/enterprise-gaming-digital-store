// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'review_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_ReviewProductDto _$ReviewProductDtoFromJson(Map<String, dynamic> json) =>
    _ReviewProductDto(
      name: json['name'] as String,
      slug: json['slug'] as String,
    );

Map<String, dynamic> _$ReviewProductDtoToJson(_ReviewProductDto instance) =>
    <String, dynamic>{'name': instance.name, 'slug': instance.slug};

_MyReviewDto _$MyReviewDtoFromJson(Map<String, dynamic> json) => _MyReviewDto(
  id: (json['id'] as num).toInt(),
  orderItemId: (json['order_item_id'] as num).toInt(),
  product: ReviewProductDto.fromJson(json['product'] as Map<String, dynamic>),
  rating: (json['rating'] as num).toInt(),
  status: json['status'] as String,
  createdAt: DateTime.parse(json['created_at'] as String),
  title: json['title'] as String?,
  body: json['body'] as String?,
);

Map<String, dynamic> _$MyReviewDtoToJson(_MyReviewDto instance) =>
    <String, dynamic>{
      'id': instance.id,
      'order_item_id': instance.orderItemId,
      'product': instance.product.toJson(),
      'rating': instance.rating,
      'status': instance.status,
      'created_at': instance.createdAt.toIso8601String(),
      'title': instance.title,
      'body': instance.body,
    };

_ReviewOrderRefDto _$ReviewOrderRefDtoFromJson(Map<String, dynamic> json) =>
    _ReviewOrderRefDto(
      publicId: json['public_id'] as String,
      orderNumber: json['order_number'] as String,
    );

Map<String, dynamic> _$ReviewOrderRefDtoToJson(_ReviewOrderRefDto instance) =>
    <String, dynamic>{
      'public_id': instance.publicId,
      'order_number': instance.orderNumber,
    };

_ReviewableItemDto _$ReviewableItemDtoFromJson(Map<String, dynamic> json) =>
    _ReviewableItemDto(
      orderItemId: (json['order_item_id'] as num).toInt(),
      order: ReviewOrderRefDto.fromJson(json['order'] as Map<String, dynamic>),
      product: ReviewProductDto.fromJson(
        json['product'] as Map<String, dynamic>,
      ),
    );

Map<String, dynamic> _$ReviewableItemDtoToJson(_ReviewableItemDto instance) =>
    <String, dynamic>{
      'order_item_id': instance.orderItemId,
      'order': instance.order.toJson(),
      'product': instance.product.toJson(),
    };
