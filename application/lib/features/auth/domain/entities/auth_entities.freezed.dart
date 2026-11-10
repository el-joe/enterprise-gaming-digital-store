// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint, type=warning, deprecated_member_use, deprecated_member_use_from_same_package
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'auth_entities.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// GENERATED CODE - DO NOT MODIFY BY HAND
// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$AuthUser {

 String get publicId; String get name; String get email; String get type; bool get emailVerified; bool get twoFactorEnabled;
/// Create a copy of AuthUser
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AuthUserCopyWith<AuthUser> get copyWith => _$AuthUserCopyWithImpl<AuthUser>(this as AuthUser, _$identity);



@override
bool operator ==(Object other) {
  final _this = this as AuthUser;
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AuthUser&&(identical(other.publicId, _this.publicId) || other.publicId == _this.publicId)&&(identical(other.name, _this.name) || other.name == _this.name)&&(identical(other.email, _this.email) || other.email == _this.email)&&(identical(other.type, _this.type) || other.type == _this.type)&&(identical(other.emailVerified, _this.emailVerified) || other.emailVerified == _this.emailVerified)&&(identical(other.twoFactorEnabled, _this.twoFactorEnabled) || other.twoFactorEnabled == _this.twoFactorEnabled));
}


@override
int get hashCode {
  final _this = this as AuthUser;
  return Object.hash(runtimeType,_this.publicId,_this.name,_this.email,_this.type,_this.emailVerified,_this.twoFactorEnabled);
}

@override
String toString() {
  final _this = this as AuthUser;
  return 'AuthUser(publicId: ${_this.publicId}, name: ${_this.name}, email: ${_this.email}, type: ${_this.type}, emailVerified: ${_this.emailVerified}, twoFactorEnabled: ${_this.twoFactorEnabled})';
}


}

/// @nodoc
abstract mixin class $AuthUserCopyWith<$Res>  {
  factory $AuthUserCopyWith(AuthUser value, $Res Function(AuthUser) _then) = _$AuthUserCopyWithImpl;
@useResult
$Res call({
 String publicId, String name, String email, String type, bool emailVerified, bool twoFactorEnabled
});




}
/// @nodoc
class _$AuthUserCopyWithImpl<$Res>
    implements $AuthUserCopyWith<$Res> {
  _$AuthUserCopyWithImpl(this._self, this._then);

  final AuthUser _self;
  final $Res Function(AuthUser) _then;

/// Create a copy of AuthUser
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? type = null,Object? emailVerified = null,Object? twoFactorEnabled = null,}) {
  return _then(AuthUser(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,twoFactorEnabled: null == twoFactorEnabled ? _self.twoFactorEnabled : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}

}


/// Adds pattern-matching-related methods to [AuthUser].
extension AuthUserPatterns on AuthUser {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AuthUser value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AuthUser() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AuthUser value)  $default,){
final _that = this;
switch (_that) {
case _AuthUser():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AuthUser value)?  $default,){
final _that = this;
switch (_that) {
case _AuthUser() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( String publicId,  String name,  String email,  String type,  bool emailVerified,  bool twoFactorEnabled)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AuthUser() when $default != null:
return $default(_that.publicId,_that.name,_that.email,_that.type,_that.emailVerified,_that.twoFactorEnabled);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( String publicId,  String name,  String email,  String type,  bool emailVerified,  bool twoFactorEnabled)  $default,) {final _that = this;
switch (_that) {
case _AuthUser():
return $default(_that.publicId,_that.name,_that.email,_that.type,_that.emailVerified,_that.twoFactorEnabled);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( String publicId,  String name,  String email,  String type,  bool emailVerified,  bool twoFactorEnabled)?  $default,) {final _that = this;
switch (_that) {
case _AuthUser() when $default != null:
return $default(_that.publicId,_that.name,_that.email,_that.type,_that.emailVerified,_that.twoFactorEnabled);case _:
  return null;

}
}

}

/// @nodoc


class _AuthUser implements AuthUser {
  const _AuthUser({required this.publicId, required this.name, required this.email, required this.type, required this.emailVerified, required this.twoFactorEnabled});
  

@override final  String publicId;
@override final  String name;
@override final  String email;
@override final  String type;
@override final  bool emailVerified;
@override final  bool twoFactorEnabled;

/// Create a copy of AuthUser
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AuthUserCopyWith<_AuthUser> get copyWith => __$AuthUserCopyWithImpl<_AuthUser>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is _AuthUser&&(identical(other.publicId, publicId) || other.publicId == publicId)&&(identical(other.name, name) || other.name == name)&&(identical(other.email, email) || other.email == email)&&(identical(other.type, type) || other.type == type)&&(identical(other.emailVerified, emailVerified) || other.emailVerified == emailVerified)&&(identical(other.twoFactorEnabled, twoFactorEnabled) || other.twoFactorEnabled == twoFactorEnabled));
}


@override
int get hashCode {
    return Object.hash(runtimeType,publicId,name,email,type,emailVerified,twoFactorEnabled);
}

@override
String toString() {
    return 'AuthUser(publicId: $publicId, name: $name, email: $email, type: $type, emailVerified: $emailVerified, twoFactorEnabled: $twoFactorEnabled)';
}


}

/// @nodoc
abstract mixin class _$AuthUserCopyWith<$Res> implements $AuthUserCopyWith<$Res> {
  factory _$AuthUserCopyWith(_AuthUser value, $Res Function(_AuthUser) _then) = __$AuthUserCopyWithImpl;
@override @useResult
$Res call({
 String publicId, String name, String email, String type, bool emailVerified, bool twoFactorEnabled
});




}
/// @nodoc
class __$AuthUserCopyWithImpl<$Res>
    implements _$AuthUserCopyWith<$Res> {
  __$AuthUserCopyWithImpl(this._self, this._then);

  final _AuthUser _self;
  final $Res Function(_AuthUser) _then;

/// Create a copy of AuthUser
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? publicId = null,Object? name = null,Object? email = null,Object? type = null,Object? emailVerified = null,Object? twoFactorEnabled = null,}) {
  return _then(_AuthUser(
publicId: null == publicId ? _self.publicId : publicId // ignore: cast_nullable_to_non_nullable
as String,name: null == name ? _self.name : name // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as String,emailVerified: null == emailVerified ? _self.emailVerified : emailVerified // ignore: cast_nullable_to_non_nullable
as bool,twoFactorEnabled: null == twoFactorEnabled ? _self.twoFactorEnabled : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
as bool,
  ));
}


}

/// @nodoc
mixin _$LoginOutcome {





@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is LoginOutcome);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
    return 'LoginOutcome()';
}


}

/// @nodoc
class $LoginOutcomeCopyWith<$Res>  {
$LoginOutcomeCopyWith(LoginOutcome _, $Res Function(LoginOutcome) __);
}


/// Adds pattern-matching-related methods to [LoginOutcome].
extension LoginOutcomePatterns on LoginOutcome {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( LoginSignedIn value)?  signedIn,TResult Function( LoginTwoFactorRequired value)?  twoFactorRequired,required TResult orElse(),}){
final _that = this;
switch (_that) {
case LoginSignedIn() when signedIn != null:
return signedIn(_that);case LoginTwoFactorRequired() when twoFactorRequired != null:
return twoFactorRequired(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( LoginSignedIn value)  signedIn,required TResult Function( LoginTwoFactorRequired value)  twoFactorRequired,}){
final _that = this;
switch (_that) {
case LoginSignedIn():
return signedIn(_that);case LoginTwoFactorRequired():
return twoFactorRequired(_that);}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( LoginSignedIn value)?  signedIn,TResult? Function( LoginTwoFactorRequired value)?  twoFactorRequired,}){
final _that = this;
switch (_that) {
case LoginSignedIn() when signedIn != null:
return signedIn(_that);case LoginTwoFactorRequired() when twoFactorRequired != null:
return twoFactorRequired(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( AuthUser user)?  signedIn,TResult Function( String challengeToken,  DateTime expiresAt)?  twoFactorRequired,required TResult orElse(),}) {final _that = this;
switch (_that) {
case LoginSignedIn() when signedIn != null:
return signedIn(_that.user);case LoginTwoFactorRequired() when twoFactorRequired != null:
return twoFactorRequired(_that.challengeToken,_that.expiresAt);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( AuthUser user)  signedIn,required TResult Function( String challengeToken,  DateTime expiresAt)  twoFactorRequired,}) {final _that = this;
switch (_that) {
case LoginSignedIn():
return signedIn(_that.user);case LoginTwoFactorRequired():
return twoFactorRequired(_that.challengeToken,_that.expiresAt);}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( AuthUser user)?  signedIn,TResult? Function( String challengeToken,  DateTime expiresAt)?  twoFactorRequired,}) {final _that = this;
switch (_that) {
case LoginSignedIn() when signedIn != null:
return signedIn(_that.user);case LoginTwoFactorRequired() when twoFactorRequired != null:
return twoFactorRequired(_that.challengeToken,_that.expiresAt);case _:
  return null;

}
}

}

/// @nodoc


class LoginSignedIn implements LoginOutcome {
  const LoginSignedIn(this.user);
  

 final  AuthUser user;

/// Create a copy of LoginOutcome
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LoginSignedInCopyWith<LoginSignedIn> get copyWith => _$LoginSignedInCopyWithImpl<LoginSignedIn>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is LoginSignedIn&&(identical(other.user, user) || other.user == user));
}


@override
int get hashCode {
    return Object.hash(runtimeType,user);
}

@override
String toString() {
    return 'LoginOutcome.signedIn(user: $user)';
}


}

/// @nodoc
abstract mixin class $LoginSignedInCopyWith<$Res> implements $LoginOutcomeCopyWith<$Res> {
  factory $LoginSignedInCopyWith(LoginSignedIn value, $Res Function(LoginSignedIn) _then) = _$LoginSignedInCopyWithImpl;
@useResult
$Res call({
 AuthUser user
});


$AuthUserCopyWith<$Res> get user;

}
/// @nodoc
class _$LoginSignedInCopyWithImpl<$Res>
    implements $LoginSignedInCopyWith<$Res> {
  _$LoginSignedInCopyWithImpl(this._self, this._then);

  final LoginSignedIn _self;
  final $Res Function(LoginSignedIn) _then;

/// Create a copy of LoginOutcome
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? user = null,}) {
  return _then(LoginSignedIn(
null == user ? _self.user : user // ignore: cast_nullable_to_non_nullable
as AuthUser,
  ));
}

/// Create a copy of LoginOutcome
/// with the given fields replaced by the non-null parameter values.
@override
@pragma('vm:prefer-inline')
$AuthUserCopyWith<$Res> get user {
  
  return $AuthUserCopyWith<$Res>(_self.user, (value) {
    return _then(_self.copyWith(user: value));
  });
}
}

/// @nodoc


class LoginTwoFactorRequired implements LoginOutcome {
  const LoginTwoFactorRequired({required this.challengeToken, required this.expiresAt});
  

 final  String challengeToken;
 final  DateTime expiresAt;

/// Create a copy of LoginOutcome
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LoginTwoFactorRequiredCopyWith<LoginTwoFactorRequired> get copyWith => _$LoginTwoFactorRequiredCopyWithImpl<LoginTwoFactorRequired>(this, _$identity);



@override
bool operator ==(Object other) {
    return identical(this, other) || (other.runtimeType == runtimeType&&other is LoginTwoFactorRequired&&(identical(other.challengeToken, challengeToken) || other.challengeToken == challengeToken)&&(identical(other.expiresAt, expiresAt) || other.expiresAt == expiresAt));
}


@override
int get hashCode {
    return Object.hash(runtimeType,challengeToken,expiresAt);
}

@override
String toString() {
    return 'LoginOutcome.twoFactorRequired(challengeToken: $challengeToken, expiresAt: $expiresAt)';
}


}

/// @nodoc
abstract mixin class $LoginTwoFactorRequiredCopyWith<$Res> implements $LoginOutcomeCopyWith<$Res> {
  factory $LoginTwoFactorRequiredCopyWith(LoginTwoFactorRequired value, $Res Function(LoginTwoFactorRequired) _then) = _$LoginTwoFactorRequiredCopyWithImpl;
@useResult
$Res call({
 String challengeToken, DateTime expiresAt
});




}
/// @nodoc
class _$LoginTwoFactorRequiredCopyWithImpl<$Res>
    implements $LoginTwoFactorRequiredCopyWith<$Res> {
  _$LoginTwoFactorRequiredCopyWithImpl(this._self, this._then);

  final LoginTwoFactorRequired _self;
  final $Res Function(LoginTwoFactorRequired) _then;

/// Create a copy of LoginOutcome
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? challengeToken = null,Object? expiresAt = null,}) {
  return _then(LoginTwoFactorRequired(
challengeToken: null == challengeToken ? _self.challengeToken : challengeToken // ignore: cast_nullable_to_non_nullable
as String,expiresAt: null == expiresAt ? _self.expiresAt : expiresAt // ignore: cast_nullable_to_non_nullable
as DateTime,
  ));
}


}

// dart format on
